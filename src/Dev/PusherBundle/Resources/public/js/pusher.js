function dev_pusher(APP_KEY, cluster) {
    var pusher = new Pusher(APP_KEY, {
        cluster: cluster
    });
    var socketId = null;

    pusher.connection.bind('connected', function() {
        socketId = pusher.connection.socket_id;

        jQuery.ajax({
            url: "/pusher/auth/presence",
            type: "POST",
            data: {
                socket_id: socketId // pass socket_id parameter to be used by server
            }
        });
    });
    return pusher;
};
