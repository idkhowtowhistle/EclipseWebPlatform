var mercury_games =
{
    // protocol registered by the Eclipse launcher, e.g. eclipse-launcher:2013<uuid>
    scheme: function ()
    {
        var s = window.eclipseLauncherScheme || "eclipse-launcher";
        return s.charAt(s.length - 1) === ":" ? s : s + ":";
    },

    launch: function (uri)
    {
        window.protocolCheck(
            uri,
            function ()
            {
                console.log("uri doesnt exist"); mercury_games.install();
            },
            function ()
            {
                $(".place-launcher-success").fadeIn(500);
                setTimeout(function () { $(".place-launcher-success").fadeOut(500); }, 5000);
            },
            2500
        );
    },

    install: function ()
    {
        $("#place-launcher-install").modal("show");
    },

    join_server: function (serverId, privateLink)
    {
        $.get(
            "/rbxclient/game/placelauncher",
            {"request": "RequestGame", "placeId": serverId, "privateLink": privateLink},
            function (data)
            {
                if (data.status == 2 && data.ticket)
                {
                    // the launcher fetches <domain>/game/join itself, the URI only carries the ticket
                    mercury_games.launch(mercury_games.scheme() + data.clientVersion + data.ticket);
                }
                else
                {
                    $(".place-launcher-error").html("<i class='fas fa-times-circle text-danger'></i> " + data.message);
                    $(".place-launcher-error").fadeIn(500);
                }
            }
        )
    }
}
