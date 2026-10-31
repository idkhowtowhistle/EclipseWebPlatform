// origin-guard: local reverse proxy in front of the site's PHP server.
//
// The main Cloudflare tunnel points here (127.0.0.1:8090) instead of the PHP
// server directly. If the PHP server is down, requests are answered with
// HTTP 521 "Web Server Is Down" instead of cloudflared's generic 502.
package main

import (
	"fmt"
	"log"
	"net/http"
	"net/http/httputil"
	"net/url"
)

const originAddr = "http://127.0.0.1:8080"

const downPage = `<!doctype html>
<html lang="en"><head><meta charset="utf-8"><title>Web Server Is Down</title>
<style>
body{font-family:system-ui,'Segoe UI',Roboto,sans-serif;background:#111;color:#eee;text-align:center;padding:90px 20px}
h1{font-size:38px;margin-bottom:6px}
code{color:#a06bff;font-weight:600}
p{color:#9a97a3;max-width:480px;margin:12px auto;line-height:1.6}
a{color:#a06bff}
</style></head>
<body>
<h1>Web server is down</h1>
<p><code>ERROR 521</code> — the Eclipse web server is not reachable right now.<br>
This is usually temporary; try again in a few minutes.</p>
<p><a href="/">Retry</a></p>
</body></html>`

func main() {
	origin, err := url.Parse(originAddr)
	if err != nil {
		log.Fatal(err)
	}

	proxy := httputil.NewSingleHostReverseProxy(origin)
	originalDirector := proxy.Director

	// keep the public Host header (join URLs are built from HTTP_HOST)
	proxy.Director = func(r *http.Request) {
		publicHost := r.Host
		originalDirector(r)
		if publicHost != "" {
			r.Host = publicHost
		}
	}

	// any failure reaching the PHP server becomes a 521
	proxy.ErrorHandler = func(w http.ResponseWriter, r *http.Request, err error) {
		log.Printf("upstream unreachable: %v", err)
		select {
		case <-r.Context().Done():
			return
		default:
		}
		w.Header().Set("Content-Type", "text/html; charset=utf-8")
		w.Header().Set("Cache-Control", "no-store")
		w.WriteHeader(521)
		fmt.Fprint(w, downPage)
	}

	log.Println("origin-guard listening on 127.0.0.1:8090 ->", originAddr)
	if err := http.ListenAndServe("127.0.0.1:8090", proxy); err != nil {
		log.Fatal(err)
	}
}
