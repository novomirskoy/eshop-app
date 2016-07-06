var websocket = WS.connect("ws://127.0.0.1:1337");

websocket.on("socket/connect", function(session){
    session.subscribe("acme/channel", function(uri, payload){
        console.log("Received message", payload.msg);
    });

    session.call("sample/sum", {"term1": 2, "term2": 5}).then(
        function(result)
        {
            console.log("RPC Valid!", result);
        },
        function(error, desc)
        {
            console.log("RPC Error", error, desc);
        }
    );

    session.publish("acme/channel", {msg: "This is a message!"});

    session.publish("acme/channel", {msg: "I'm leaving, I will not see the next message"});

    session.unsubscribe("acme/channel");

    session.publish("acme/channel", {msg: "I won't see this"});

    session.subscribe("acme/channel", function(uri, payload){
        console.log("Received message", payload.msg);
    });
    session.publish("acme/channel", {msg: "I'm back!"});
});

websocket.on("socket/disconnect", function(error){
    //error provides us with some insight into the disconnection: error.reason and error.code

    console.log("Disconnected for " + error.reason + " with code " + error.code);
});