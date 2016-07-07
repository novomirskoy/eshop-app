var websocket = WS.connect("ws://localhost:8081");

websocket.on("socket/connect", function(session){

    // websocketSession = session;
    //
    // session.subscribe("acme/channel", function(uri, payload){
    //     console.log("Received message", payload.msg);
    // });
    //
    // session.call("sample/sum", {"term1": 2, "term2": 5}).then(
    //     function(result)
    //     {
    //         console.log("RPC Valid!", result);
    //     },
    //     function(error, desc)
    //     {
    //         console.log("RPC Error", error, desc);
    //     }
    // );
    //
    // session.publish("acme/channel", {msg: "This is a message!"});
    //
    // session.publish("acme/channel", {msg: "I'm leaving, I will not see the next message"});
    //
    // session.unsubscribe("acme/channel");
    //
    // session.publish("acme/channel", {msg: "I won't see this"});
    //
    // session.subscribe("acme/channel", function(uri, payload){
    //     console.log("Received message", payload.msg);
    // });
    // session.publish("acme/channel", {msg: "I'm back!"});
});

websocket.on("socket/disconnect", function(error){
    //error provides us with some insight into the disconnection: error.reason and error.code

    console.log("Disconnected for " + error.reason + " with code " + error.code);
});

// var Product = new Vue.extend({
//     data: {
//         id: '',
//         name: '',
//         description: '',
//         price: '',
//         quantity: '',
//         image: ''
//     }
// });

var vm = new Vue({
    el: "#app",
    data: {
        products: []
    },
    components: {
        alert: VueStrap.alert
    },
    created: function () {
        var self = this;
        websocket.on("socket/connect", function(session) {
            session.subscribe("product/channel", function (uri, payload) {
                self.$dispatch("products", payload);
                // self.products = payload.products;
            });

            // session.call("product/getAll", {}).then(
            //     function (result) {
            //         self.products = result;
            //     }
            // );
        });
    },
    events: {
        "products": function (data) {
            this.products = data.products;
        }
    }
});

