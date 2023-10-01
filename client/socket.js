const app ={
    ws: null,
    init: function(){
        $("#connect").on('click',function(e){
            app.connectChannel();
        })
    },
    connectChannel: function(){
        this.ws = new WebSocket("ws://localhost:9502");
        this.ws.onopen = function(){
            let redisChannel = $("#channel").val();
            app.ws.send(redisChannel);
        };
        this.ws.onmessage = function (evt) { 
            var received_msg = evt.data;
            $("#message").val($("#message").val() + received_msg+"\n");
         };
    }
}
app.init();