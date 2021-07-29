var mercury_games = 
{
    launch: function (joinscripturl) 
    {
        $(".place-launcher").show();
        $(".place-launcher-success").fadeIn(400);
        window.protocolCheck(
            joinscripturl,
            function() { console.log("uri doesnt exist"); mercury_games.install(); },
            function() { console.log("uri exists"); },
            2500
        );  
    },

    install: function () 
    {
        $("#place-launcher-install").modal("show");
    },

    join_server: function (serverId, privateLink) 
    {
        $.post("/api/games/launchGame", {
            "serverId": serverId,
            "privateLink": privateLink
        }, function (data) 
        {
            if (data.success) 
            {
                mercury_games.launch("mercury-player:1+launchmode:play+joinscripturl:" + encodeURIComponent(data.joinscripturl) + "+gameinfo:1")
            } 
            else 
            {
                $(".place-launcher").show();
                $(".place-launcher-error").html("<i class='fas fa-times-circle text-danger'></i> " + data.message);
                $(".place-launcher-error").fadeIn(400);
            }
        })
    }
}

