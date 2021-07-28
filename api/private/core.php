<?php

if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == "http" && strpos($_SERVER["DOCUMENT_URI"], "/rbxclient/") === false) 
{
	header('HTTP/1.1 301 Moved Permanently');
    header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
    exit;
}

require $_SERVER['DOCUMENT_ROOT'].'/api/private/config.php';
require $_SERVER['DOCUMENT_ROOT'].'/api/private/vendors/Parsedown.php'; 

try
{
	$pdo = new PDO('mysql:host='.SITE_CONFIG["database"]["host"].';dbname='.SITE_CONFIG["database"]["schema"], SITE_CONFIG["database"]["username"], SITE_CONFIG["database"]["password"]);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $e)
{
	die("Connection to ".SITE_CONFIG["site"]["name"]." terminated: Database connection to '".SITE_CONFIG["database"]["schema"]."' at ".SITE_CONFIG["database"]["host"]." failed: ".$e->getMessage());
}
require $_SERVER['DOCUMENT_ROOT'].'/api/private/pagebuilder.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') foreach($_POST as $key => $val){ $_POST[$key] = trim($val); }
foreach($_GET as $key => $val){ $_GET[$key] = trim($val); }
 
// functions that arent strictly specifically for polygon and moreof to just 
// extend basic php functionality like string manipulation or some small 
// utilities are typically just put here classless

function str_starts_with($haystack, $needle) 
{
	return substr($haystack, 0, strlen($needle)) === $needle;
}

function generateUUID()
{
	return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', 
		mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), 
		mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, 
		mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
}

function rgbtohex($rgb)
{
	$rgb_parsed = sscanf($rgb, "rgb(%i, %i, %i)");
	return sprintf('%02X%02X%02X', $rgb_parsed[0], $rgb_parsed[1], $rgb_parsed[2]);
}

function redirect($url)
{
	die(header("Location: $url"));
}

function getFileSize($bytes, $binaryPrefix = true) 
{
    $unit=array('B','KB','MB','GB','TB','PB');
    if (!$bytes) return '0 ' . $unit[0];
    if ($binaryPrefix) return round($bytes/pow(1024,($i=floor(log($bytes,1024)))),2) .' '. ($unit[$i] ?? 'B');
    return round($bytes/pow(1000,($i=floor(log($bytes,1000)))),2) .' '. ($unit[$i] ?? 'B');
}

function getFolderSize($path)
{
	$io = popen("du -sb $path", "r");
	$size = (int)filter_var(explode($path, fgets($io, 4096), 2)[0], FILTER_SANITIZE_NUMBER_INT);
	pclose($io);
	return getFileSize($size);
}

class db
{
	static function run($sql, $params = false)
	{
		global $pdo;
		if(!$params) return $pdo->query($sql);
		
		$query = $pdo->prepare($sql);
		$query->execute($params);
		return $query;
	}
}

 
class RBX
{
	static function GetSignature($data)
	{
		openssl_sign($data, $signature, openssl_pkey_get_private("file://".$_SERVER['DOCUMENT_ROOT']."/../mercury_private.pem"));
		return base64_encode($signature);
	}

	static function SignData($data, $assetID = false)
	{
		if($assetID) $data = "%{$assetID}%\n{$data}";
		else $data = "\n{$data}";
		$signedScript = "%" . self::GetSignature($data) . "%{$data}"; 
		return $signedScript;
	}
}

class api 
{
	static function respond($status, $success, $message) 
	{
		die(json_encode(["status" => $status, "success" => $success, "message" => $message]));
	}

	static function lastAction()
	{
		global $pdo;
		if(!SESSION || !SESSION["adminLevel"]){ return false; }
		$userid = SESSION["userId"];
		$query = $pdo->prepare("SELECT lastaction FROM users WHERE id = :uid AND lastaction + 20 > UNIX_TIMESTAMP()");
		$query->bindParam(":uid", $userid, PDO::PARAM_INT);
		$query->execute();

		if($query->rowCount()){ self::respond(400, false, "Please wait ".(($query->fetchColumn()+20)-time())." seconds doing another action"); }

		$query = $pdo->prepare("UPDATE users SET lastaction = UNIX_TIMESTAMP() WHERE id = :uid");
		$query->bindParam(":uid", $userid, PDO::PARAM_INT);
		$query->execute();
	}

	
	static function requireLogin()
	{
		if(!SESSION || SESSION["2fa"] && !SESSION["2faVerified"]) self::respond(400, false, "Not logged in");
		if(!isset($_SERVER['HTTP_X_POLYGON_CSRF'])) self::respond(400, false, "CSRF token not set");
		if($_SERVER['HTTP_X_POLYGON_CSRF'] != SESSION["csrfToken"]) self::respond(400, false, "Invalid CSRF token");
	}

}

class general
 {
	static function time_elapsed($datetime, $full = false, $ending = true) //https://stackoverflow.com/questions/1416697/converting-timestamp-to-time-ago-in-php-e-g-1-day-ago-2-days-ago
	{
		if($datetime == "@"){ return "-"; }
	    $now = new DateTime;
	    $ago = new DateTime($datetime);
	    $diff = $now->diff($ago);

	    $diff->w = floor($diff->d / 7);
	    $diff->d -= $diff->w * 7;

	    $string = array(
	        'y' => 'year',
	        'm' => 'month',
	        'w' => 'week',
	        'd' => 'day',
	        'h' => 'hour',
	        'i' => 'minute',
	        's' => 'second',
	    );
	    foreach ($string as $k => &$v) 
	    {
	        if ($diff->$k) { $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : ''); } 
	        else { unset($string[$k]); }
	    }

	    if (!$full) $string = array_slice($string, 0, 1);
	    if($ending){ return $string ? implode(', ', $string) . ' ago' : 'just now'; }
	    return implode(', ', $string);
	}

	static function getServerMemoryUsage() 
	{
		$lines = explode("\n", file_get_contents('/proc/meminfo'));
		$total = (int) filter_var($lines[0], FILTER_SANITIZE_NUMBER_INT);
		$free = (int) filter_var($lines[1], FILTER_SANITIZE_NUMBER_INT);
        return (object)["total" => $total*1024, "free" => $free*1024];
    }

	static function filterText($text, $htmlspecialchars = true, $highlight = true)
	{
		if($htmlspecialchars){ $text = htmlspecialchars($text); }
		$filtertext = $highlight ? "<strong><em>Kyle</em></strong>" : "Kyle";
		return str_ireplace(["fuck", "shit", "bitch", "nigger", "nigger", "faggot", "nigga", "nibba", "nibber", "dick", "cock", "penis", "sex", "cum", "nya"], $filtertext, $text);
	}

}

class upload 
{
	static function initialize()
	{
		require $_SERVER["DOCUMENT_ROOT"]."/api/private/vendors/class.upload.php";
	}

	public static array $types = 
	[
	    1 => "Image", 
		2 => "T-Shirt",
		3 => "Audio",
		4 => "Mesh",
		5 => "Lua",
		6 => "HTML",
		7 => "Text",
		8 => "Hat",
	    9 => "Place",
		10 => "Model",
		11 => "Shirt",
		12 => "Pants",
		13 => "Decal",
		16 => "Avatar",
		17 => "Head",
		18 => "Face",
		19 => "Gear",
		21 => "Badge"
	];
	
	static function getAssetIdFromName($name) 
	{
		return db::run("SELECT id FROM assets WHERE name = :name", [":name" => $name])->fetchColumn();
	}

	static function GetAllAssets()
	{
		return db::run("SELECT COUNT(*) FROM assets")->fetchColumn();
	}
}

class twofa 
{
	static function initialize()
	{
		require $_SERVER['DOCUMENT_ROOT'].'/api/private/vendors/2fa/FixedBitNotation.php'; 
		require $_SERVER['DOCUMENT_ROOT'].'/api/private/vendors/2fa/GoogleQrUrl.php'; 
		require $_SERVER['DOCUMENT_ROOT'].'/api/private/vendors/2fa/GoogleAuthenticatorInterface.php'; 
		require $_SERVER['DOCUMENT_ROOT'].'/api/private/vendors/2fa/GoogleAuthenticator.php'; 
		return new \Google\Authenticator\GoogleAuthenticator();
	}

	static function toggle()
	{
		if(!SESSION) return false;

		$uid = SESSION["userId"];
		$twofa = !SESSION["2fa"];

		return db::run("UPDATE users SET twofa = :2fa WHERE id = :uid", [":2fa" => $twofa, ":uid" => $uid])->rowCount();
	}

	static function generateRecoveryCodes()
	{
		if(!SESSION) return false;

		$uid = SESSION["userId"];
		$codes = str_split(bin2hex(random_bytes(60)), 12);
		$json = json_encode(array_fill_keys($codes, true));

		db::run("UPDATE users SET twofaRecoveryCodes = :json WHERE id = :uid", [":json" => $json, ":uid" => $uid]);

		return $codes;
	}
}

class admin
{
  	static function updateAuditLogs($moderatedId, $type, $option, $action) 
  	{
        $moderatorId = SESSION["userId"];
       
        switch ($type) {
			case 1: 
				$type = "Administration";
				break;
			case 2: 
				$type = "Moderation";
				break;
		}

        global $pdo;

		$query = $pdo->prepare("INSERT INTO audit (userIdModerated, moderatorId, type, modoption, action, date) VALUES (:userIdModerated, :moderatorId, :type, :modoption, :action, UNIX_TIMESTAMP())");
		$query->bindParam(":userIdModerated", $moderatedId, PDO::PARAM_INT);
        $query->bindParam(":moderatorId", $moderatorId, PDO::PARAM_INT);
		$query->bindParam(":type", $type, PDO::PARAM_STR);
		$query->bindParam(":modoption", $option, PDO::PARAM_STR);
		$query->bindParam(":action", $action, PDO::PARAM_STR);
        $query->execute();

		return true;

  	}

  	static function getBannerInfo($bannerId) 
  	{
		return db::run("SELECT * FROM banners WHERE id = :id", [":id" => $bannerId])->fetch(PDO::FETCH_OBJ);
  	}
}

class news 
{
	static function getNewsInfo($newsId) 
	{
		return db::run("SELECT * FROM news WHERE id = :id", [":id" => $newsId])->fetch(PDO::FETCH_OBJ);
	}
}

class messages 
{
	static function getMessageInfo($msgId)
	{
		return db::run("SELECT * FROM messages WHERE id = :id", [":id" => $msgId])->fetch(PDO::FETCH_OBJ);
	}
}

class forum 
{
 	static function getSubForumInfo($subId)
	{
		return db::run("SELECT * FROM subforums WHERE id = :id", [":id" => $subId])->fetch(PDO::FETCH_OBJ);
	}

	static function getSubForumThreads($subForumId)
	{
		return db::run(
			"SELECT subforum_threads.*, users.username, users.admin FROM subforum_threads 
			INNER JOIN users ON users.id = subforum_threads.authorId WHERE subForumId = :id AND NOT deleted 
			ORDER BY bumpIndex DESC", 
			[":id" => $subForumId]
		);
	}

	static function getLastSubForumPost($subForumId)
	{
		return db::run(
			"SELECT subforum_threads.*, users.username, users.admin FROM subforum_threads 
			INNER JOIN users ON users.id = subforum_threads.authorId WHERE subForumId = :id AND NOT deleted 
			ORDER BY bumpIndex DESC LIMIT 1", 
			[":id" => $subForumId]
		)->fetch(PDO::FETCH_OBJ);
	}

	static function getThreadInfo($threadId) 
	{
		return db::run(
			"SELECT subforum_threads.*, users.username, users.admin FROM subforum_threads 
			INNER JOIN users ON users.id = subforum_threads.authorId WHERE subforum_threads.id = :id", 
			[":id" => $threadId]
		)->fetch(PDO::FETCH_OBJ);
	}

	static function getThreadReplies($threadId) 
	{
		return db::run(
		    "SELECT subforum_replies.*, users.username, users.admin FROM subforum_replies
		    INNER JOIN users ON users.id = subforum_replies.authorId
		    WHERE replyTo = :threadId ORDER BY datePosted ASC", 
		    [":threadId" => $threadId]
		);
	}

	static function getReplyCount($subId)
	{
		$query = db::run(
			"SELECT COUNT(*) FROM subforum_replies WHERE replyTo = :subId AND NOT deleted", 
			[":subId" => $subId]
		);

		return (int)$query->fetchColumn();
	}

	static function getSubForumNameFromId($id)
	{
		return db::run("SELECT subName FROM subforums WHERE id = :id", [":id" => $id])->fetchColumn();
	}

	static function getTotalPosts()
	{
		$query = db::run("SELECT (SELECT COUNT(*) FROM subforum_threads) + (SELECT COUNT(*) FROM subforum_replies)");
		return (int)$query->fetchColumn();
	}

	static function getThreadsInSubForum($subId)
	{
      return db::run("SELECT COUNT(*) FROM subforum_threads WHERE subForumId = :id", [":id" => $subId])->fetchColumn();
	}

	static function getPostsInSubForum($subId)
	{
		return db::run("SELECT (SELECT COUNT(*) FROM subforum_threads WHERE subForumId = :id) + (SELECT COUNT(*) FROM subforum_replies WHERE subForumId = :id)", [":id" => $subId])->fetchColumn();
	}
}

class games 
{
	static function getGameInfoFromId($gameId, $moreInfo = false)
	{
		if(!$moreInfo)
			return db::run("SELECT * FROM games WHERE id = :id", [":id" => $gameId])->fetch(PDO::FETCH_OBJ);
			
		return db::run(
			"SELECT game_sessions.*, users.username, (serverPing+35 > UNIX_TIMESTAMP()) AS serverOnline,
			(SELECT COUNT(*) FROM game_sessions WHERE serverId = games.id AND valid AND ping+35 > UNIX_TIMESTAMP() GROUP BY userId) AS playerCount,
			(SELECT COUNT(*) FROM game_sessions WHERE serverId = games.id AND ping > 0) AS visitCount
			FROM games INNER JOIN users ON users.id = ownerId WHERE games.id = :id",
			[":id" => $gameId]
		)->fetch(PDO::FETCH_OBJ);
	}

	static function getGameInfoFromTicket($tick)
	{
		return db::run("SELECT * FROM games WHERE serverTicket = :tick", [":tick" => $tick])->fetch(PDO::FETCH_OBJ);
	}

	static function getClientInfo($tick)
	{
		return db::run("SELECT * FROM game_sessions WHERE ticket = :tick", [":tick" => $tick])->fetch(PDO::FETCH_OBJ);
	}

	static function isHosting($serverId) 
	{
		global $pdo;

		$response = ["online" => false];

		$query = $pdo->prepare("SELECT serverPing FROM games WHERE id = :sid");
		$query->bindParam(":sid", $serverId, PDO::PARAM_INT);
		$query->execute();

		$time = $query->fetchColumn();

		if(!$query->rowCount()){ return $response; }
		if($time+30 > time()){ $response["online"] = true;}
		// \a\t g:i:s A
		return $response;
	}

	static function getPlayersInServer($serverId)
	{
		$query = db::run("
		    SELECT game_sessions.*, users.username FROM game_sessions 
		    INNER JOIN users ON users.id = game_sessions.userId 
		    WHERE serverId = :serverid AND valid AND ping+35 > UNIX_TIMESTAMP() 
		    GROUP BY userId", 
		    [":serverid" => $serverId]
		);

		return $query;
	}

	static function getPlayerCountInServer($serverId)
	{
		$query = db::run("
		    SELECT COUNT(*) FROM game_sessions 
		    WHERE serverId = :serverid AND valid AND ping+35 > UNIX_TIMESTAMP()", 
		    [":serverid" => $serverId]
		);

		return (int)$query->fetchColumn();
	}

	static function checkIfPlayerInServer($userId, $serverId)
	{
		$query = db::run("
		    SELECT COUNT(*) FROM game_sessions 
		    WHERE userId = :userid AND serverId = :serverid AND valid AND ping+35 > UNIX_TIMESTAMP()", 
		    [":userid" => $userId, ":serverid" => $serverId]
		);

		return (int)$query->fetchColumn();
	}

	static function createClientSession($serverId, $userId, $ticket) 
	{
		global $pdo;
		
		$query = $pdo->prepare("INSERT INTO game_sessions (serverId, userId, ticket) VALUES (:serverId, :userId, :ticket)");
		$query->bindParam(":serverId", $serverId, PDO::PARAM_INT);
		$query->bindParam(":userId", $userId, PDO::PARAM_INT);
		$query->bindParam(":ticket", $ticket, PDO::PARAM_STR);
		$query->execute();
	}

}

class users 
{
	static function getGames($userId, $isPrivate = false)
	{
		return db::run(
			"SELECT * FROM games WHERE ownerId = :uid AND isPrivate = :private AND NOT isDeleted",
			[":uid" => $userId, ":private" => $isPrivate]
		);
	}

	static function getOnlineStatus($userId)
	{
		global $pdo;

		$response = ["online" => false, "playing" => false, "text" => false];
       
        $query = db::run(
        	"SELECT game_sessions.ping, serverId, games.serverName FROM game_sessions
			INNER JOIN games ON games.id = game_sessions.serverId
			WHERE userId = :id ORDER BY game_sessions.ping DESC LIMIT 1",
			[":id" => $userId]);
		$info = $query->fetch(PDO::FETCH_OBJ);

        if($info && $info->ping+35 > time()) 
        	return ["online" => true, "playing" => true, "serverid" => $info->serverId, "text" => "Playing ".$info->serverName];

		$query = db::run("SELECT lastonline FROM users WHERE id = :id", [":id" => $userId]);
		$time = $query->fetchColumn();	

		if(!$query->rowCount()){ return $response; }
		if($time+30 > time()){ $response["online"] = true; $response["text"] = "Website"; }
		else{ $response["text"] = ($time + 604800) > time() ? general::time_elapsed('@'.$time) : date('j/n/Y', $time);  }
		// \a\t g:i:s A
		return $response;
	}

	static function updateStipend()
	{
		global $pdo;
		if(!SESSION){ return false; }

		$userId = SESSION["userId"];
		if(SESSION["nextStipend"] > time()){ return true; } //not yet

		return db::run("UPDATE users SET currency = currency + 10, nextStipend = UNIX_TIMESTAMP()+86400 WHERE id = :uid", [":uid" => $userId])->execute();
	}

	static function getUserForumPosts($uid)
	{
		$query = db::run("
		SELECT (SELECT COUNT(*) FROM subforum_threads WHERE authorId = :uid) + (SELECT COUNT(*) FROM subforum_replies WHERE authorId = :uid)
		", [":uid" => $uid]);

		return (int)$query->fetchColumn();
	}

    static function getUnreadMessages($userId)
	{
		global $pdo;

		$query = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE :uid = messageTo AND NOT sentToStatus");
		$query->bindParam(":uid", $userId, PDO::PARAM_INT);
		$query->execute();

		return $query->fetchColumn();
	}

	static function generateRandomFact()
	{
		switch(rand(1, 4)) 
		{
			case 1:
                $nf = new NumberFormatter("en_US", NumberFormatter::ORDINAL);
				return "You are the " . $nf->format(SESSION["userId"]) . " user to join!";
				break;

			case 2: 
				return "Hello, " . SESSION["userName"];
				break;

			case 3:
				$userInfo = self::getUserInfoFromUid(SESSION["userId"]);
				return "You joined Mercury on " . date("n/j/Y", $userInfo->joined);
				break;

			case 4: 
				if(SESSION["userId"] == 1 || SESSION["userId"] == 2) return "I totally am the owner. I promise.";
				return "Who are you again? Oh, you're " . SESSION["userName"] . "!"; 
				break;

			default:
			    return "There is no fact to display.";
		}
	}

	static function getAvatar($userId)
	{
      return "https://banland.xyz/thumbs/avatar/1.png";
	}

	static function getUsersOnline()
	{
		return db::run("SELECT COUNT(*) FROM users WHERE lastonline+35 > UNIX_TIMESTAMP()")->fetchColumn();
	}

	static function requireLogin($adminLevel = false)
	{
		if($adminLevel && (!SESSION || SESSION["adminLevel"] < $adminLevel)) pagebuilder::errorCode(404);
		if(!SESSION) die(header("Location: /login?ReturnUrl=".urlencode($_SERVER['REQUEST_URI'])));
	}

	static function getAllUsers()
	{
		return db::run("SELECT COUNT(*) FROM users")->fetchColumn();
	}

	static function getUserInfoFromUid($userId)
	{
		return db::run("SELECT * FROM users WHERE id = :uid", [":uid" => $userId])->fetch(PDO::FETCH_OBJ);
	}

    static function getUserInfoFromUserName($username) 
    {
		return db::run("SELECT * FROM users WHERE username = :username", [":username" => $username])->fetch(PDO::FETCH_OBJ);
	}

    static function getUsernameFromUid($userId) 
    {
		return db::run("SELECT username FROM users WHERE id = :uid", [":uid" => $userId])->fetchColumn();
	}

	static function getUidFromUsername($username) 
    {
		return db::run("SELECT id FROM users WHERE username = :username", [":username" => $username])->fetchColumn();
	}

	static function getRank($rank)
  	{
  		return [1 => "Member", 2 => "Moderator", 3 => "Admin"][$rank];
  	}

	static function undoUserModeration($userId, $admin = false)
	{
	    if ($admin)
		{
          db::run("UPDATE bans SET undone = 1 WHERE userId = :id AND NOT undone", [":id" => $userId]);
		}
		else 
		{
          db::run("UPDATE bans SET undone = 1 WHERE userId = :id AND NOT undone AND NOT type = 3 AND timeEnds < UNIX_TIMESTAMP()", [":id" => $userId]);
		}
	}

	static function getUserModeration($userId)
	{
		global $pdo;

		$query = $pdo->prepare("SELECT * FROM bans WHERE userId = :id AND NOT undone ORDER BY id DESC LIMIT 1");
		$query->bindParam(":id", $userId, PDO::PARAM_INT);
		$query->execute();

		return $query->fetch(PDO::FETCH_OBJ);
	}
}

class invite 
{
	static function generateKey() 
	{
		//uuid gen https://www.uuidgenerator.net/dev-corner/php
	    $data = $data ?? random_bytes(16);
	    assert(strlen($data) == 16);
	    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
	    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
	    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	}

	static function getKeyInfo($keyId) 
	{
	  	return db::run("SELECT * FROM invitekeys WHERE id = :id", [":id" => $keyId])->fetch(PDO::FETCH_OBJ);
	}

	static function getKeyInfoFromKey($key) 
	{
	  	return db::run("SELECT * FROM invitekeys WHERE inviteKey = :key", [":key" => $key])->fetch(PDO::FETCH_OBJ);
	}

	static function validateInviteKey($key) 
	{
		$info = db::run("SELECT * FROM invitekeys WHERE inviteKey = :inviteKey", [":inviteKey" => $key])->fetch(PDO::FETCH_OBJ);
		if(!$info || $info->deleted || $info->keyUsed) return false; 
		return true;
	}

	static function useKey($key, $username) 
	{
		db::run(
			"UPDATE invitekeys SET keyUsed = 1, usedById = :usedById WHERE inviteKey = :inviteKey",
			[":usedById" => users::getUidFromUsername($username), ":inviteKey" => $key]
		);
	}
}


//session system written by pizzaboxer for use on project polygon - 26/10/2020

class session //most of the session code here comes from my old roblonium code; it works surprisingly well
{
	static function create($userId, $RBXAuthenticationNegotiation = false)
	{
		global $pdo;

		keygen:
		$sessionkey = bin2hex(random_bytes(128)); 

		$checkIfKeyExists = db::run("SELECT COUNT(*) FROM sessions WHERE sessionKey = :sesskey", [":sesskey" => $sessionkey]);
		if($checkIfKeyExists->fetchColumn()) goto keygen; //if a session with the same key already exists then repeat key generation process

		$csrf = bin2hex(random_bytes(32));

		$params = 
		[
			":sesskey" => $sessionkey, 
			":uid" => $userId, 
			":agent" => $_SERVER['HTTP_USER_AGENT'], 
			":ip" => $_SERVER['REMOTE_ADDR'],
			":csrf" => $csrf,
			":expires" => 0,
			":2fa" => 0
		];

		// roblox client sessions have a ttl of 360 seconds which is periodically renewed by the client
		if($RBXAuthenticationNegotiation) 
		{
			$params[":expires"] = time()+360;
			$params[":2fa"] = 1;
		}

		db::run(
			"INSERT INTO sessions (sessionKey, userId, userAgent, ip, created, lastonline, csrf, twofaVerified, expires) 
			VALUES (:sesskey, :uid, :agent, :ip, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :csrf, :2fa, :expires)",
			$params
		);

		setcookie("polygon_session", $sessionkey, time()+(157700000*3), "/"); //expires in 5 years
	}

	static function invalidate($sesskey)
	{
		return db::run("UPDATE sessions SET valid = 0 WHERE sessionKey = :sesskey", [":sesskey" => $sesskey])->rowCount();
	}

	static function logout($sesskey, $refresh = true)
	{
		if($_SERVER["HTTP_USER_AGENT"] == "Roblox/WinInet") $refresh = false;
		self::invalidate($sesskey);
		setcookie("polygon_session", $sesskey, 1, "/");
		if($refresh) die(header("Refresh: 0"));
	}

	static function get($sessionkey, $strict = true) 
	{		
		$query = db::run("SELECT * FROM sessions WHERE sessionKey = :sesskey AND valid AND lastonline+432000 > UNIX_TIMESTAMP()", [":sesskey" => $sessionkey]);
		
		if(!$query->rowCount()) return false;
		$row = $query->fetch(PDO::FETCH_OBJ);

		if(!$row->valid) return false;
		if($row->created+(157700000*3) < time()) return false;
		//if($strict && $row->userAgent != $_SERVER['HTTP_USER_AGENT']) return false;
		if($row->expires != 0 && $row->expires < time()) return false;
		
		return $row;
	}

	static function updatePing()
	{
		if(!SESSION) return false;
		db::run("UPDATE users SET lastonline = UNIX_TIMESTAMP() WHERE id = :id", [":id" => SESSION["userId"]]);
		db::run("UPDATE sessions SET lastonline = UNIX_TIMESTAMP() WHERE sessionKey = :key", [":key" => SESSION["sessionKey"]]);
		return true;
	}
}

if(isset($_COOKIE['polygon_session']))
{	
	$session = session::get($_COOKIE['polygon_session']);
	if($session) 
	{
		$userInfo = users::getUserInfoFromUid($session->userId);
		//one thing i dislike about globals is that you cant use them as objects, only arrays but /shrug
		//kjf: use an stdclass then
		define('SESSION', 
			[
				"userName" => $userInfo->username, 
				"userId" => $userInfo->id, 
				"2fa" => $userInfo->twofa,
				"ssc" => $userInfo->ssc,
				"2faVerified" => $session->twofaVerified,
				"adminLevel" => $userInfo->admin,
				"currency" => $userInfo->currency, 
				"messages" => $userInfo->messages,
				"nextStipend" => $userInfo->nextStipend,
				"gameban" => $userInfo->gameBan,
				//"getHosting" => users::getHosting($userInfo->id),
				//"friendRequests" => friends::getFriendRequestCount($userInfo->id),
				"sessionKey" => $session->sessionKey,
				"csrfToken" => $session->csrf,
				"userInfo" => (array)$userInfo
			]);
	    if(users::getUserModeration(SESSION["userId"]) && !isset($bypassModeration))
		{
			die(header("Location: /moderation"));
		}
        elseif(SESSION["2fa"] && !SESSION["2faVerified"] && !in_array($_SERVER['REQUEST_URI'], ["/auth/twofa", "/logout"])) {
			die(header("Location: /auth/twofa"));
		}
		//else
	//	{
            
			session::updatePing();
			users::updateStipend();
		//}
	}
	else 
	{
		session::logout($_COOKIE['polygon_session']);
		define('SESSION', false);
	}
}
else 
{
	define('SESSION', false);
}