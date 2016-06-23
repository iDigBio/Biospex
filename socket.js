var app = require('express')();
var fs = require('fs');

var options = {
    key: fs.readFileSync('/etc/letsencrypt/live/biospex.org/privkey.pem'),
    cert: fs.readFileSync('/etc/letsencrypt/live/biospex.org/cert.pem'),
    ca: fs.readFileSync('/etc/letsencrypt/live/biospex.org/fullchain.pem')
};

var server = require('https').createServer(app);
var io = require('socket.io')(server);
io.on('connection', function(){});
server.listen(8080);

var Redis = require('ioredis');
var redis = new Redis();

redis.psubscribe('*', function(err, count) {});

redis.on('pmessage', function(subscribed, channel, message) {
    message = JSON.parse(message);
    io.emit(channel + ':' + message.event, message.data);
});
