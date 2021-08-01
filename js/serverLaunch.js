var mercury_games = 
{
    launch: function (joinscripturl) 
    {
        window.protocolCheck(
            joinscripturl,
            function() 
            { 
                console.log("uri doesnt exist"); mercury_games.install(); 
            },
            function() 
            { 
                $(".place-launcher-success").fadeIn(500);
                setTimeout(function() { $(".place-launcher-success").fadeOut(500); }, 5000);
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
        if(window.navigator.userAgent.includes("ROBLOX Android App"))
        {
            // for some reason the app doesnt pass the auth cookie 
            // into the actual client activity - so here's this gross hack
            // window.location = "/games/start?placeid=" + serverId;

            $.get(
                "/rbxclient/game/placelauncher", 
                {"request": "RequestGame","placeId": serverId, "privateLink": privateLink}, 
                function(data) 
                {
                    if (data.status == 2) 
                    {
                        var ticket = data.joinScriptUrl.split("?ticket=")[1];
                        window.location = "/games/start?placeid=" + btoa(ticket);
                    } 
                    else 
                    {
                        $(".place-launcher-error").html("<i class='fas fa-times-circle text-danger'></i> " + data.message);
                        $(".place-launcher-error").fadeIn(500);
                    }
                }
            )

            return;
        }

        $.get(
            "/rbxclient/game/placelauncher", 
            {"request": "RequestGame","placeId": serverId, "privateLink": privateLink}, 
            function(data) 
            {
                if (data.status == 2) 
                {
                    mercury_games.launch("mercury-player:1+launchmode:play+joinscripturl:" + encodeURIComponent(data.joinScriptUrl) + "+gameinfo:" + data.authenticationTicket)
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

