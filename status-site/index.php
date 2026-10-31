<?php
/**
 * Eclipse Status — página de status autônoma.
 *
 * Servida por seu próprio PHP (php -S 127.0.0.1:8100 -t status-site) e
 * publicada em https://status.idkhowtowhistle.dpdns.org. Não depende do site
 * principal: se ele cair, esta página continua indicando o que está fora.
 *
 *   GET /          -> página HTML (auto-atualiza a cada 30s)
 *   GET /?json=1   -> JSON com os resultados das checagens
 *
 * As checagens rodam aqui dentro (HTTP via curl_multi, TCP, MySQL) e ficam em
 * cache por ~40s para as visitas não bombardearem os serviços.
 */

error_reporting(0);
header("x-content-type-options: nosniff");

$DOMAIN = "idkhowtowhistle.dpdns.org";

// credenciais do banco vêm do config do site principal (só o array, sem conexão)
$DB = null;
$cfgPath = dirname(__DIR__) . "/api/private/config.php";
if (is_file($cfgPath)) {
	ob_start();
	$included = include $cfgPath;
	ob_end_clean();
	// config.php define SITE_CONFIG como CONSTANT e religa display_errors; restaura o silêncio
	error_reporting(0);
	ini_set("display_errors", "0");
	if ($included !== false && defined("SITE_CONFIG")) {
		$DB = SITE_CONFIG["database"];
	}
}

$services = [
	["name" => "Site principal", "type" => "http", "url" => "https://{$DOMAIN}/"],
	["name" => "www (alias)", "type" => "http", "url" => "https://www.{$DOMAIN}/"],
	["name" => "Setup / versão do cliente", "type" => "http", "url" => "https://{$DOMAIN}/setup/2013/version"],
	["name" => "API de jogo (join)", "type" => "http", "url" => "https://{$DOMAIN}/game/join"],
	["name" => "Render de avatar", "type" => "http", "url" => "https://{$DOMAIN}/asset/characterfetch/1"],
	["name" => "docs", "type" => "http", "url" => "https://docs.{$DOMAIN}/"],
	["name" => "cdn", "type" => "http", "url" => "https://cdn.{$DOMAIN}/"],
	["name" => "Banco de dados", "type" => "mysql"],
	["name" => "Servidor PHP do site", "type" => "tcp", "host" => "127.0.0.1", "port" => 8080],
	["name" => "RCCService (render)", "type" => "tcp", "host" => "127.0.0.1", "port" => 64989],
	["name" => "Servidor do status (local)", "type" => "tcp", "host" => "127.0.0.1", "port" => 8100],
];

const CACHE_TTL = 40;
const CACHE_KEY = "eclipse-status-cache";

function statusCache() { return sys_get_temp_dir() . "/" . CACHE_KEY . ".json"; }

function statusLoad()
{
	$f = statusCache();
	if (!is_file($f)) return null;
	$raw = json_decode((string)@file_get_contents($f), true);
	if (!is_array($raw) || !isset($raw["t"]) || !isset($raw["results"])) return null;
	return $raw;
}

function statusSave($t, $results)
{
	@file_put_contents(statusCache(), json_encode(["t" => $t, "results" => $results]), LOCK_EX);
}

class CheckResult
{
	public $name, $type, $state, $ms, $detail;
	// state: "ok" | "degraded" | "down"
	function __construct($name, $type, $state, $ms, $detail = "")
	{
		$this->name = $name; $this->type = $type; $this->state = $state;
		$this->ms = $ms; $this->detail = $detail;
	}
}

function stateLabel($state)
{
	return $state === "ok" ? "Operacional" : ($state === "degraded" ? "Erros" : "Interrupção");
}

function describeHttpCode($code)
{
	$map = [
		400 => "erro de requisição", 401 => "não autorizado", 403 => "acesso negado",
		404 => "não encontrado", 429 => "limite de requisições", 500 => "erro interno",
		502 => "gateway inválido", 503 => "indisponível", 504 => "tempo esgotado",
	];
	return isset($map[$code]) ? $map[$code] : "";
}

function probeAll(array $services, $DB)
{
	$httpTargets = [];
	foreach ($services as $i => $s) if ($s["type"] === "http") $httpTargets[$i] = $s;

	$mh = curl_multi_init();
	$hdlrs = [];
	$handles = [];
	foreach ($httpTargets as $i => $s) {
		$hdlrs[$i] = [];
		$ch = curl_init($s["url"]);
		curl_setopt_array($ch, [
			CURLOPT_NOBODY => false,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS => 3,
			CURLOPT_USERAGENT => "EclipseStatus/1.0",
			CURLOPT_CONNECTTIMEOUT => 4,
			CURLOPT_TIMEOUT => 10,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADERFUNCTION => function ($ch, $line) use (&$hdlrs, $i) {
				// cada redirect começa com "HTTP/..."; só interessam os headers da resposta final
				if (strncasecmp($line, "HTTP/", 5) === 0) $hdlrs[$i] = [];
				$parts = explode(":", $line, 2);
				if (count($parts) === 2) $hdlrs[$i][strtolower(trim($parts[0]))] = trim($parts[1]);
				return strlen($line);
			},
		]);
		curl_multi_add_handle($mh, $ch);
		$handles[$i] = $ch;
	}

	do {
		curl_multi_exec($mh, $running);
		if ($running) curl_multi_select($mh, 0.25);
	} while ($running);

	$results = [];
	foreach ($handles as $i => $ch) {
		$code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$ms = (int)ceil(curl_getinfo($ch, CURLINFO_TOTAL_TIME_T) / 1000);
		$errno = curl_errno($ch);
		$err = curl_error($ch);
		curl_multi_remove_handle($mh, $ch);
		// (não chamar curl_close: descontinuada no PHP 8.5, o handle já é liberado)

		$s = $services[$i];
		if ($errno) {
			$msg = match ($errno) {
				CURLE_COULDNT_RESOLVE_HOST => "falha de DNS",
				CURLE_OPERATION_TIMEDOUT => "tempo esgotado",
				CURLE_COULDNT_CONNECT => "conexão recusada",
				CURLE_SSL_CONNECT_ERROR => "erro de SSL",
				default => ($err ?: "erro de rede"),
			};
			$results[$i] = new CheckResult($s["name"], "http", "down", $ms, $msg);
		} elseif ($code === 0) {
			$results[$i] = new CheckResult($s["name"], "http", "down", $ms, "sem resposta");
		} elseif ($code < 400) {
			$detail = "HTTP {$code}";
			if (!empty($hdlrs[$i]["cf-mitigated"])) $detail .= " (protegido por challenge)";
			$results[$i] = new CheckResult($s["name"], "http", "ok", $ms, $detail);
		} elseif ($code === 403 && !empty($hdlrs[$i]["cf-mitigated"])) {
			// 403 causado por challenge do Cloudflare: o serviço responde, só defende bots
			$results[$i] = new CheckResult($s["name"], "http", "ok", $ms, "HTTP 403 (challenge do Cloudflare)");
		} else {
			$d = "HTTP {$code}" . (describeHttpCode($code) ? " — " . describeHttpCode($code) : "");
			$results[$i] = new CheckResult($s["name"], "http", $code >= 500 ? "down" : "degraded", $ms, $d);
		}
	}
	curl_multi_close($mh);

	foreach ($services as $i => $s) {
		if (isset($results[$i])) continue;
		switch ($s["type"]) {
			case "tcp": {
				$t0 = (hrtime(true) / 1e6);
				$fp = @fsockopen($s["host"], $s["port"], $ec, $es, 2.5);
				$ms = (int)round((hrtime(true) / 1e6) - $t0);
				if ($fp) {
					fclose($fp);
					$results[$i] = new CheckResult($s["name"], "tcp", "ok", $ms, "porta {$s["port"]} aberta");
				} else {
					$results[$i] = new CheckResult($s["name"], "tcp", "down", $ms, "porta {$s["port"]} fechada");
				}
				break;
			}
			case "mysql": {
				if ($DB === null) {
					$results[$i] = new CheckResult($s["name"], "mysql", "down", 0, "config não encontrada");
					break;
				}
				$t0 = (hrtime(true) / 1e6);
				try {
					$pdo = new PDO(
						"mysql:host={$DB["host"]};dbname={$DB["schema"]}",
						$DB["username"], $DB["password"],
						[PDO::ATTR_TIMEOUT => 3, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
					);
					$pdo->query("SELECT 1");
					$ms = (int)round((hrtime(true) / 1e6) - $t0);
					$results[$i] = new CheckResult($s["name"], "mysql", "ok", $ms, "respondeu ao SELECT 1");
				} catch (Exception $e) {
					$ms = (int)round((hrtime(true) / 1e6) - $t0);
					$results[$i] = new CheckResult($s["name"], "mysql", "down", $ms, "conexão falhou");
				}
				break;
			}
		}
	}

	ksort($results);
	return array_values($results);
}

function statusOverall(array $results)
{
	$states = array_map(fn($r) => $r->state, $results);
	if (in_array("down", $states, true)) return ["down", "Interrupção em alguns serviços"];
	if (in_array("degraded", $states, true)) return ["degraded", "Instabilidade parcial"];
	return ["ok", "Todos os sistemas operacionais"];
}

$now = time();
$cached = statusLoad();
if ($cached === null || ($now - (int)$cached["t"]) >= CACHE_TTL) {
	$results = probeAll($services, $DB);
	statusSave($now, $results);
	$checkedAt = $now;
} else {
	$results = [];
	foreach ($cached["results"] as $r) {
		$c = new CheckResult($r["name"], $r["type"], $r["state"], $r["ms"], $r["detail"]);
		$results[] = $c;
	}
	$checkedAt = (int)$cached["t"];
}

$overall = statusOverall($results);

// ---------- JSON ----------
if (isset($_GET["json"])) {
	header("content-type: application/json; charset=utf-8");
	header("cache-control: no-store");
	echo json_encode([
		"site" => "Eclipse Status",
		"overall" => ["state" => $overall[0], "message" => $overall[1]],
		"checked_at" => $checkedAt,
		"services" => array_map(
			fn($r) => [
				"name" => $r->name, "type" => $r->type, "state" => $r->state,
				"ms" => $r->ms, "detail" => $r->detail,
			],
			$results
		),
	], JSON_UNESCAPED_UNICODE);
	exit;
}

// ---------- HTML ----------
header("content-type: text/html; charset=utf-8");
header("cache-control: no-store");
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Eclipse Status</title>
<style>
	:root {
		--bg: #0f0e11; --card: #17161a; --line: #29272e; --text: #ecebf0;
		--muted: #8f8c9b; --accent: #7733ff;
		--ok: #22c55e; --warn: #f59e0b; --bad: #ef4444;
	}
	* { box-sizing: border-box; }
	body { margin: 0; background: var(--bg); color: var(--text);
		font: 15px/1.5 system-ui, "Segoe UI", Roboto, sans-serif; }
	.wrap { max-width: 720px; margin: 0 auto; padding: 40px 20px 60px; }
	.brand { display: flex; align-items: center; gap: 10px; }
	.dot { width: 12px; height: 12px; border-radius: 50%; background: var(--accent); }
	.brand h1 { font-size: 20px; margin: 0; letter-spacing: .3px; }
	.banner { border-radius: 12px; padding: 18px 20px; margin: 22px 0 26px; font-weight: 600;
		border: 1px solid var(--line); background: var(--card); }
	.banner.ok { border-left: 5px solid var(--ok); }
	.banner.degraded { border-left: 5px solid var(--warn); }
	.banner.down { border-left: 5px solid var(--bad); }
	.banner small { display: block; font-weight: 400; color: var(--muted); margin-top: 4px; }
	.svc { background: var(--card); border: 1px solid var(--line); border-radius: 10px;
		padding: 14px 18px; margin-bottom: 10px; display: flex; align-items: center; gap: 14px; }
	.svc .info { flex: 1; min-width: 0; }
	.svc .name { font-weight: 600; }
	.svc .detail { color: var(--muted); font-size: 13px; overflow: hidden; text-overflow: ellipsis; }
	.chip { display: inline-flex; align-items: center; gap: 7px; font-size: 13px;
		font-weight: 600; white-space: nowrap; }
	.chip::before { content: ""; width: 9px; height: 9px; border-radius: 50%; }
	.chip.ok { color: var(--ok); } .chip.ok::before { background: var(--ok); }
	.chip.degraded { color: var(--warn); } .chip.degraded::before { background: var(--warn); }
	.chip.down { color: var(--bad); } .chip.down::before { background: var(--bad); }
	.ms { color: var(--muted); font-size: 12.5px; font-variant-numeric: tabular-nums; width: 64px; text-align: right; }
	.foot { color: var(--muted); font-size: 13px; margin-top: 26px; line-height: 1.6; }
	.foot a { color: var(--accent); text-decoration: none; }
</style>
</head>
<body>
<div class="wrap">
	<div class="brand">
		<span class="dot"></span>
		<h1>Eclipse Status</h1>
	</div>
	<div class="banner <?= htmlspecialchars($overall[0]) ?>" id="banner"><?= htmlspecialchars($overall[1]) ?></div>
	<div id="services">
	<?php foreach ($results as $r): ?>
		<div class="svc">
			<div class="info">
				<div class="name"><?= htmlspecialchars($r->name) ?></div>
				<div class="detail"><?= htmlspecialchars($r->detail ?: stateLabel($r->state)) ?></div>
			</div>
			<span class="ms"><?= $r->ms ?> ms</span>
			<span class="chip <?= htmlspecialchars($r->state) ?>"><?= stateLabel($r->state) ?></span>
		</div>
	<?php endforeach; ?>
	</div>
	<div class="foot">
		Última verificação: <span id="checked">…</span> — as checagens ficam em cache por <?= CACHE_TTL ?>s e
		os resultados são medidos por este servidor.<br>
		Se esta página não abrir, o próprio túnel do status está fora do ar — os outros serviços
		podem continuar normais. Site: <a href="https://<?= htmlspecialchars($DOMAIN) ?>/"><?= htmlspecialchars($DOMAIN) ?></a>.
	</div>
</div>
<script>
	var fmt = function (t) { return new Date(t * 1000).toLocaleTimeString("pt-BR"); };
	var checked = document.getElementById("checked");
	var banner = document.getElementById("banner");
	var box = document.getElementById("services");
	var data = {
		checked_at: <?= (int)$checkedAt ?>,
		overall: <?= json_encode(["state" => $overall[0], "message" => $overall[1]]) ?>,
		services: <?= json_encode(array_map(
			fn($r) => ["name" => $r->name, "state" => $r->state, "ms" => $r->ms, "detail" => $r->detail],
			$results
		)) ?>
	};
	checked.textContent = fmt(data.checked_at);

	function render(d) {
		banner.className = "banner " + (d.overall.state || "");
		banner.textContent = d.overall.message || "";
		box.innerHTML = "";
		(d.services || []).forEach(function (r) {
			var label = r.state === "ok" ? "Operacional" : (r.state === "degraded" ? "Erros" : "Interrupção");
			var row = document.createElement("div");
			row.className = "svc";
			row.innerHTML =
				"<div class='info'><div class='name'></div><div class='detail'></div></div>" +
				"<span class='ms'>" + r.ms + " ms</span>" +
				"<span class='chip " + r.state + "'>" + label + "</span>";
			row.querySelector(".name").textContent = r.name;
			row.querySelector(".detail").textContent = r.detail || label;
			box.appendChild(row);
		});
		checked.textContent = fmt(d.checked_at);
	}

	setInterval(function () {
		fetch("/?json=1", { cache: "no-store" })
			.then(function (r) { return r.json(); })
			.then(render)
			.catch(function () {});
	}, 30000);
</script>
</body>
</html>
