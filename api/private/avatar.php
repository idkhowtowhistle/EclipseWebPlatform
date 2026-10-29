<?php
require_once __DIR__ . '/config.php';

spl_autoload_register(function ($class) {
    $prefix = 'Virtubrick\\Grid\\';
    $base_dir = __DIR__ . '/virtubrick/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

/**
 * Avatar rendering pipeline for Mercury.
 *
 * Appearance is stored per user in `user_avatars` as hex colors (R6 body
 * colours + optional face). Rendering is done by a local RCCService:
 *   browser -> /thumbnails/avatar.php -> background render-worker.php
 *   worker  -> RCCService (SOAP BatchJobEx) -> Lua -> POST callback
 *   callback -> GD composite/resize -> /thumbnails/avatars/{username}-{size}.png
 */
class avatarRenderer
{
    /** Sizes that the endpoint accepts and the callback generates. */
    const SIZES = [48, 75, 100, 110, 250, 352, 420];

    /** Headshot sizes written by the callback. */
    const HEAD_SIZES = [48, 100, 150, 250, 300];

    /** Appearance keys that hold a #RRGGBB colour. */
    const COLOR_KEYS = ['head', 'torso', 'leftArm', 'rightArm', 'leftLeg', 'rightLeg'];

    public static function defaults()
    {
        return [
            'head'     => '#F5CD30', // bright yellow
            'torso'    => '#0D69AC', // bright blue
            'leftArm'  => '#F5CD30',
            'rightArm' => '#F5CD30',
            'leftLeg'  => '#A4BD47', // br. yellowish green
            'rightLeg' => '#A4BD47',
            'face'     => 1,
        ];
    }

    public static function dir()
    {
        $dir = dirname(__DIR__, 2) . '/thumbnails/avatars';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        return $dir;
    }

    /**
     * Filesystem name for a user's thumbnails: their username.
     *
     * The resolved name is mirrored to a small sidecar ({id}.name) so the image
     * endpoint can serve the files without bootstrapping the database. When the
     * username cannot be resolved (no database in that context, user missing)
     * the numeric id is used instead.
     */
    public static function thumbName($userId)
    {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return '0';
        }

        static $cache = [];
        if (isset($cache[$userId])) {
            return $cache[$userId];
        }

        $sidecar = self::dir() . '/' . $userId . '.name';
        $name = '';

        // When the database is available (worker, callback, site pages) the
        // username is authoritative; the sidecar keeps the image endpoint,
        // which never boots the database, working from a cached copy.
        $hasDb = class_exists('users') && method_exists('users', 'getUsernameFromUid');
        if ($hasDb) {
            try {
                $name = self::sanitizeName((string)users::getUsernameFromUid($userId));
            } catch (Exception $e) {
                self::log('thumbName(' . $userId . '): ' . $e->getMessage());
            }
        }

        if ($name === '' && is_file($sidecar)) {
            $name = self::sanitizeName((string)@file_get_contents($sidecar));
        }

        if ($name === '') {
            return $cache[$userId] = (string)$userId;
        }

        if ($hasDb) {
            @file_put_contents($sidecar, $name);
        }

        return $cache[$userId] = $name;
    }

    /** Keeps usernames safe for the filesystem and for glob(). */
    private static function sanitizeName($name)
    {
        $name = preg_replace('/[^A-Za-z0-9_.-]/', '', trim((string)$name));
        return trim((string)$name, '.');
    }

    public static function cfg($key, $default = null)
    {
        return SITE_CONFIG['render'][$key] ?? $default;
    }

    private static $ensured = false;

    public static function ensureTable()
    {
        if (self::$ensured) {
            return;
        }
        self::$ensured = true;
        try {
            db::run(
                "CREATE TABLE IF NOT EXISTS user_avatars (
                    userId int(11) NOT NULL,
                    head varchar(7) NOT NULL DEFAULT '#F5CD30',
                    torso varchar(7) NOT NULL DEFAULT '#0D69AC',
                    leftArm varchar(7) NOT NULL DEFAULT '#F5CD30',
                    rightArm varchar(7) NOT NULL DEFAULT '#F5CD30',
                    leftLeg varchar(7) NOT NULL DEFAULT '#A4BD47',
                    rightLeg varchar(7) NOT NULL DEFAULT '#A4BD47',
                    face tinyint(1) NOT NULL DEFAULT 1,
                    updatedOn int(11) NOT NULL DEFAULT 0,
                    PRIMARY KEY (userId)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1"
            );
        } catch (Exception $e) {
            self::log('ensureTable: ' . $e->getMessage());
        }
    }

    public static function hex($value, $fallback)
    {
        $value = strtoupper(trim((string)$value));
        if (!preg_match('/^#?[0-9A-F]{6}$/', $value)) {
            return $fallback;
        }
        if ($value[0] !== '#') {
            $value = '#' . $value;
        }
        return $value;
    }

    /** Returns the appearance for a user, filling in defaults. */
    public static function get($userId)
    {
        self::ensureTable();
        $appearance = self::defaults();
        try {
            $row = db::run(
                "SELECT * FROM user_avatars WHERE userId = :id",
                [":id" => (int)$userId]
            )->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                foreach (self::defaults() as $key => $default) {
                    if (!array_key_exists($key, $row)) {
                        continue;
                    }
                    $appearance[$key] = ($key === 'face')
                        ? ((int)$row[$key] ? 1 : 0)
                        : self::hex($row[$key], $default);
                }
            }
        } catch (Exception $e) {
            self::log('get(' . (int)$userId . '): ' . $e->getMessage());
        }
        return $appearance;
    }

    public static function set($userId, $appearance)
    {
        self::ensureTable();
        $defaults = self::defaults();
        $values = [];
        foreach (self::COLOR_KEYS as $key) {
            $values[$key] = self::hex($appearance[$key] ?? '', $defaults[$key]);
        }
        $values['face'] = !empty($appearance['face']) ? 1 : 0;

        $params = [":id" => (int)$userId, ":face" => $values['face']];
        foreach (self::COLOR_KEYS as $key) {
            $params[':' . $key] = $values[$key];
        }

        db::run(
            "REPLACE INTO user_avatars
                (userId, head, torso, leftArm, rightArm, leftLeg, rightLeg, face, updatedOn)
             VALUES
                (:id, :head, :torso, :leftArm, :rightArm, :leftLeg, :rightLeg, :face, UNIX_TIMESTAMP())",
            $params
        );
    }

    public static function version($appearance)
    {
        $defaults = self::defaults();
        $normalized = [];
        foreach ($defaults as $key => $default) {
            $normalized[$key] = ($key === 'face')
                ? (!empty($appearance[$key]) ? 1 : 0)
                : self::hex($appearance[$key] ?? '', $default);
        }
        return substr(md5(json_encode($normalized)), 0, 12);
    }

    public static function token($userId, $version)
    {
        return hash_hmac(
            'sha256',
            (int)$userId . ':' . $version,
            (string)self::cfg('key', '')
        );
    }

    public static function callbackUrl($userId, $version)
    {
        return self::cfg('callback') . '?id=' . (int)$userId
            . '&v=' . urlencode($version)
            . '&token=' . self::token($userId, $version);
    }

    /**
     * Builds the Lua script executed by RCCService.
     *
     * This is the tp-link-extender/2013 render pipeline: renderAvatar.luau plus
     * the Modules it requires (Render, Render/SetupAvatar, New), translated to
     * the Lua dialect of the bundled 2013 RCCService.
     *
     * The avatar is a real R6 rig produced by Player:LoadCharacter from the
     * characterfetch document, not a hand-built model. This RCCService build
     * ships with Players:GetCharacterAppearanceAsync() disabled, so once the
     * rig exists the same body colours are handed to the engine through
     * Player:LoadCharacterAppearance(), the backend API used by 2013
     * thumbnails.
     */
    public static function lua($appearance, $callbackUrl)
    {
        $defaults = self::defaults();
        $a = [];
        foreach ($defaults as $key => $default) {
            $a[$key] = ($key === 'face')
                ? (!empty($appearance[$key]) ? 1 : 0)
                : self::hex($appearance[$key] ?? '', $default);
        }

        $userId = (int)($appearance['userId'] ?? 0);
        $baseUrl = (string)self::cfg('baseUrl', '127.0.0.1:8080');
        $assetId = $userId > 0 ? (string)$userId : '1';

        $head = self::brickColor($a['head']);
        $torso = self::brickColor($a['torso']);
        $leftArm = self::brickColor($a['leftArm']);
        $rightArm = self::brickColor($a['rightArm']);
        $leftLeg = self::brickColor($a['leftLeg']);
        $rightLeg = self::brickColor($a['rightLeg']);
        $face = $a['face'] ? 'true' : 'false';

        $lua = <<<LUA
-- ===========================================================================
-- Modules/Render/init.luau
-- ===========================================================================
local function RenderModule(baseUrl, pingUrl)
	local HttpService = game:GetService("HttpService")
	local ScriptContext = game:GetService("ScriptContext")
	local ContentProvider = game:GetService("ContentProvider")
	local InsertService = game:GetService("InsertService")

	local function post(url, body)
		game:HttpPost(url, body, true, "text/json")
	end

	local Render = {}

	local ok, err = pcall(function()
		ContentProvider:SetBaseUrl("http://" .. baseUrl .. "/")
		InsertService:SetAssetUrl(baseUrl .. "/asset?id=%d")
		InsertService:SetAssetVersionUrl(baseUrl .. "/asset?assetversionid=%d")
	end)
	if not ok then
		error("Failed to set asset URLs: " .. tostring(err))
	end

	HttpService.HttpEnabled = true
	ScriptContext.ScriptsDisabled = true

	ok, err = pcall(function()
		post(pingUrl, "Rendering")
	end)
	if not ok then
		error("Failed to send rendering ping: " .. tostring(err))
	end

	function Render.Upload(result)
		for i = 1, 3 do
			local uploaded = pcall(function()
				post(pingUrl, result)
			end)
			if uploaded then
				return
			end
			if i == 3 then
				return
			end
		end
	end

	return Render
end

-- ===========================================================================
-- Modules/Render/SetupAvatar.luau
-- ===========================================================================
local function SetupAvatar(baseUrl, renderType, assetId, characterAppearance)
	local Players = game:GetService("Players")

	local player = Players:CreateLocalPlayer(0)
	player.CharacterAppearance = "http://" .. baseUrl .. characterAppearance .. assetId
	player:LoadCharacter(false)

	local backpack = player:WaitForChild("Backpack")
	local character = player.Character
	if not character then
		return nil, nil, player
	end

	local gear = backpack:GetChildren()[1]
	if not gear then
		return character, nil, player
	end

	gear.Parent = character

	local torso = character:FindFirstChild("Torso")
	if not torso then
		return character, nil, player
	end

	local rightShoulder = torso:FindFirstChild("Right Shoulder")
	if not rightShoulder then
		return character, nil, player
	end

	rightShoulder.CurrentAngle = math.rad(90)
	return character, rightShoulder, player
end

-- ===========================================================================
-- Modules/New.luau
-- ===========================================================================
local function New(className)
	return function(props)
		local object = Instance.new(className)
		for key, value in pairs(props) do
			object[key] = value
		end
		return object
	end
end

-- ===========================================================================
-- Render/renderAvatar.luau
-- ===========================================================================
local BASE_URL = "{$baseUrl}"
local PING_URL = "{$callbackUrl}"
local RENDER_TYPE = "Avatar"
local ASSET_ID = "{$assetId}"

local ThumbnailGenerator = game:GetService("ThumbnailGenerator")

-- The repo renders inside its own place, whose Lighting is neutral. The bundled
-- RCCService starts on a bare place with black ambient, so normalise it here to
-- keep the classic thumbnail colours.
local Lighting = game:GetService("Lighting")
Lighting.Ambient = Color3.new(1, 1, 1)
Lighting.OutdoorAmbient = Color3.new(1, 1, 1)
Lighting.Brightness = 1

local Render = RenderModule(BASE_URL, PING_URL)

local char, shoulder, player = SetupAvatar(BASE_URL, RENDER_TYPE, ASSET_ID, "/asset/characterfetch/")
if not char then
	error("Failed to set up avatar for " .. RENDER_TYPE .. " ID " .. ASSET_ID)
end

-- Body colours from the characterfetch document, applied through the engine's
-- backend appearance API.
local bodyColors = Instance.new("BodyColors")
bodyColors.HeadColor = BrickColor.new({$head})
bodyColors.TorsoColor = BrickColor.new({$torso})
bodyColors.LeftArmColor = BrickColor.new({$leftArm})
bodyColors.RightArmColor = BrickColor.new({$rightArm})
bodyColors.LeftLegColor = BrickColor.new({$leftLeg})
bodyColors.RightLegColor = BrickColor.new({$rightLeg})
player:LoadCharacterAppearance(bodyColors)

-- Face from the characterfetch document.
if {$face} then
	local head = char:FindFirstChild("Head")
	if head then
		local face = Instance.new("Decal")
		face.Name = "face"
		face.Texture = "rbxasset://textures/face.png"
		face.Face = Enum.NormalId.Front
		face.Parent = head
	end
end

local clickBody = ThumbnailGenerator:Click("PNG", 840, 840, true)
print("Rendered bodyshot")

if shoulder then
	shoulder.CurrentAngle = 0
end

local CameraAngle = char.Head.CFrame
local CameraPosition = CameraAngle + CFrame.Angles(math.rad(180), math.pi, 0).lookVector.Unit * 2.75

workspace.CurrentCamera = New("Camera")({
	Name = "ThumbnailCamera",
	CameraType = Enum.CameraType.Scriptable,
	CoordinateFrame = CFrame.new(CameraPosition.p, CameraAngle.p),
	FieldOfView = 52.5,
	Parent = char,
})

local clickHead = ThumbnailGenerator:Click("PNG", 300, 300, true)
print("Rendered headshot")

Render.Upload("Completed\\n" .. clickBody .. "\\n" .. clickHead)
LUA;

        return $lua;
    }

    /**
     * Loads the BrickColor palette dumped from RCCService so hex colours can be
     * converted to the palette indexes that characterfetch XML requires.
     */
    private static function palette()
    {
        static $palette = null;
        if ($palette !== null) {
            return $palette;
        }

        $palette = [];
        $file = dirname(__DIR__, 2) . '/thumbnails/palette.json';
        if (is_file($file)) {
            $data = json_decode((string)@file_get_contents($file), true);
            if (is_array($data)) {
                foreach ($data as $entry) {
                    if (!isset($entry['n'], $entry['r'], $entry['g'], $entry['b'])) {
                        continue;
                    }
                    $palette[] = [
                        'n' => (int)$entry['n'],
                        'r' => (int)$entry['r'],
                        'g' => (int)$entry['g'],
                        'b' => (int)$entry['b'],
                    ];
                }
            }
        }

        return $palette;
    }

    /** Nearest BrickColor number for a #RRGGBB value. */
    public static function brickColor($hex)
    {
        $palette = self::palette();
        if (!$palette) {
            return 1;
        }

        $hex = ltrim(strtoupper((string)$hex), '#');
        if (strlen($hex) !== 6) {
            return 1;
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $best = 1;
        $bestDistance = PHP_INT_MAX;
        foreach ($palette as $entry) {
            $dr = $entry['r'] - $r;
            $dg = $entry['g'] - $g;
            $db = $entry['b'] - $b;
            $distance = $dr * $dr + $dg * $dg + $db * $db;
            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $best = $entry['n'];
            }
        }

        return $best;
    }

    /**
     * characterfetch document consumed by Player:LoadCharacter.
     * Body colours are palette indexes; the face is the classic smile decal.
     */
    public static function characterFetchXml($userId)
    {
        $a = self::get($userId);

        $head = self::brickColor($a['head']);
        $torso = self::brickColor($a['torso']);
        $leftArm = self::brickColor($a['leftArm']);
        $rightArm = self::brickColor($a['rightArm']);
        $leftLeg = self::brickColor($a['leftLeg']);
        $rightLeg = self::brickColor($a['rightLeg']);

        $face = $a['face'] ? <<<XML

    <Item class="Decal">
        <Properties>
            <Content name="Texture">
                <url>rbxasset://textures/face.png</url>
            </Content>
            <token name="Face">5</token>
        </Properties>
    </Item>
XML : '';

        return <<<XML
<?xml version="1.0" encoding="utf-8"?>
<roblox xmlns:xmime="http://www.w3.org/2005/05/xmlmime" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://www.roblox.com/roblox.xsd" version="4">
    <Item class="BodyColors">
        <Properties>
            <int name="HeadColor">{$head}</int>
            <int name="LeftArmColor">{$leftArm}</int>
            <int name="LeftLegColor">{$leftLeg}</int>
            <int name="RightArmColor">{$rightArm}</int>
            <int name="RightLegColor">{$rightLeg}</int>
            <int name="TorsoColor">{$torso}</int>
        </Properties>
    </Item>{$face}
</roblox>
XML;
    }

    public static function soapTemplate()
    {
        return <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope
    xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:xsd="http://www.w3.org/2001/XMLSchema"
    xmlns:ns1="http://roblox.com/">
    <soap:Body>
        <ns1:BatchJobEx>
            <ns1:job>
                <ns1:id>_TASK_ID</ns1:id>
                <ns1:expirationInSeconds>30</ns1:expirationInSeconds>
                <ns1:category>1</ns1:category>
                <ns1:cores>1</ns1:cores>
            </ns1:job>
            <ns1:script>
                <ns1:name>RenderScript</ns1:name>
                <ns1:script>
                    _RENDER_SCRIPT
                </ns1:script>
            </ns1:script>
        </ns1:BatchJobEx>
    </soap:Body>
</soap:Envelope>
XML;
    }

    public static function submit($lua, $jobId)
    {
        $escaped = str_replace(
            ['&', '<', '>'],
            ['&amp;', '&lt;', '&gt;'],
            $lua
        );
        $xml = str_replace(
            ['_TASK_ID', '_RENDER_SCRIPT'],
            [$jobId, $escaped],
            self::soapTemplate()
        );

        $ch = curl_init((string)self::cfg('rccUrl'));
        if ($ch === false) {
            self::log('submit: curl_init failed');
            return false;
        }
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $xml,
            CURLOPT_HTTPHEADER => [
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: http://roblox.com/OpenJobEx',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 20,
        ]);
        $response = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        self::log(
            'submit ' . $jobId . ' -> HTTP ' . $status
            . ($error !== '' ? ' error=' . $error : '')
            . ($status !== 200 ? ' body=' . substr((string)$response, 0, 600) : '')
        );

        return $status === 200;
    }

    public static function rccUp()
    {
        $fp = @fsockopen('127.0.0.1', 64989, $errno, $errstr, 0.5);
        if ($fp) {
            fclose($fp);
            return true;
        }
        return false;
    }

    public static function ensureRcc()
    {
        if (self::rccUp()) {
            return true;
        }

        $vbs = (string)self::cfg('rccStartVbs', '');
        self::log('RCC not listening, starting via ' . $vbs);
        if ($vbs !== '' && is_file($vbs)) {
            @pclose(@popen('wscript.exe "' . $vbs . '"', 'r'));
        }

        for ($i = 0; $i < 30; $i++) {
            usleep(300000);
            if (self::rccUp()) {
                self::log('RCC is up after ' . ($i + 1) . ' tries');
                return true;
            }
        }

        self::log('RCC failed to start');
        return false;
    }

    public static function marker($userId)
    {
        return self::dir() . '/.rendering-' . (int)$userId;
    }

    public static function isRendering($userId)
    {
        $marker = self::marker($userId);
        if (!is_file($marker)) {
            return false;
        }
        if (filemtime($marker) + 90 < time()) {
            @unlink($marker);
            return false;
        }
        return true;
    }

    public static function mark($userId)
    {
        $marker = self::marker($userId);
        $handle = @fopen($marker, 'x');
        if ($handle) {
            fwrite($handle, (string)time());
            fclose($handle);
            return true;
        }
        if (is_file($marker) && filemtime($marker) + 90 < time()) {
            @unlink($marker);
            return self::mark($userId);
        }
        return false;
    }

    public static function unmark($userId)
    {
        @unlink(self::marker($userId));
    }

    /** Starts the CLI worker in the background. Returns false if already running. */
    public static function spawn($userId)
    {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return false;
        }
        if (!self::mark($userId)) {
            return false;
        }

        $php = PHP_BINARY;
        $worker = dirname(__DIR__, 2) . '/thumbnails/render-worker.php';
        $command = 'start "" /B "' . $php . '" "' . $worker . '" ' . $userId;
        @pclose(@popen($command, 'r'));
        return true;
    }

    public static function clear($userId)
    {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return;
        }
        $dir = self::dir();
        $names = array_unique([self::thumbName($userId), (string)$userId]);
        foreach ($names as $name) {
            foreach ((array)glob($dir . '/' . $name . '-*.png') as $file) {
                @unlink($file);
            }
            @unlink($dir . '/' . $name . '-render.json');
        }
    }

    /**
     * Scales the RCCService bodyshot (840x840, transparent background) onto the
     * 420x420 canvas and writes every supported size.
     */
    public static function store($userId, $png, $version = '')
    {
        $userId = (int)$userId;
        $dir = self::dir();
        $name = self::thumbName($userId);
        $image = @imagecreatefromstring($png);
        if (!$image) {
            self::log('store(' . $userId . '): invalid PNG');
            return false;
        }

        imagealphablending($image, false);
        imagesavealpha($image, true);

        $width = imagesx($image);
        $height = imagesy($image);

        $canvas = imagecreatetruecolor(420, 420);
        if (!$canvas) {
            return false;
        }
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, 419, 419, $transparent);

        if ($width === 420 && $height === 420) {
            imagealphablending($canvas, true);
            imagesavealpha($canvas, true);
            imagecopy($canvas, $image, 0, 0, 0, 0, 420, 420);
        } else {
            imagealphablending($canvas, true);
            imagesavealpha($canvas, true);
            imagecopyresampled(
                $canvas, $image,
                0, 0, 0, 0,
                420, 420,
                $width, $height
            );
        }

        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagepng($canvas, $dir . '/' . $name . '-420x420.png');

        foreach (self::SIZES as $size) {
            if ($size === 420) {
                continue;
            }
            $resized = imagescale($canvas, $size, $size, IMG_BICUBIC);
            if ($resized) {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                imagepng($resized, $dir . '/' . $name . '-' . $size . 'x' . $size . '.png');
                imagedestroy($resized);
            }
        }

        imagedestroy($image);
        imagedestroy($canvas);

        @file_put_contents(
            $dir . '/' . $name . '-render.json',
            json_encode(['version' => $version, 'time' => time()])
        );

        // Drop thumbnails written under the old id-based name.
        if ($name !== (string)$userId) {
            foreach ((array)glob($dir . '/' . $userId . '-*.png') as $legacy) {
                @unlink($legacy);
            }
            @unlink($dir . '/' . $userId . '-render.json');
        }

        self::log('store(' . $userId . '): wrote sizes for ' . $name . ' version ' . $version);
        return true;
    }

    /** Stores the 300x300 headshot next to the body thumbnails. */
    public static function storeHeadshot($userId, $png)
    {
        $userId = (int)$userId;
        $name = self::thumbName($userId);
        $image = @imagecreatefromstring($png);
        if (!$image) {
            self::log('storeHeadshot(' . $userId . '): invalid PNG');
            return false;
        }

        imagealphablending($image, false);
        imagesavealpha($image, true);

        foreach (self::HEAD_SIZES as $size) {
            $resized = imagescale($image, $size, $size, IMG_BICUBIC);
            if (!$resized) {
                continue;
            }
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagepng($resized, self::dir() . '/' . self::headFile($name, $size));
            imagedestroy($resized);
        }

        imagedestroy($image);
        return true;
    }

    /** File name of a headshot size (150 keeps the short {name}-head.png). */
    public static function headFile($name, $size)
    {
        $size = (int)$size;
        return $size === 150
            ? $name . '-head.png'
            : $name . '-head-' . $size . 'x' . $size . '.png';
    }

    public static function log($message)
    {
        @file_put_contents(
            self::dir() . '/render.log',
            '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n",
            FILE_APPEND | LOCK_EX
        );
    }
}
