var https = require('https');
var fs = require('fs');
var socketio = require('socket.io');
var Redis = require('ioredis');

var svrPort = 443; // This is the port of service
var svrOptions = {
    key: fs.readFileSync('/etc/letsencrypt/live/biospex.org/privkey.pem'),
    cert: fs.readFileSync('/etc/letsencrypt/live/biospex.org/cert.pem'),
    ca: fs.readFileSync( '/etc/letsencrypt/live/biospex.org/fullchain.pem')
};

var servidor = https.createServer( svrOptions , function( req , res ){
    res.writeHead(200);
    res.end('OK');
});

io = socketio.listen( servidor );

// Now listen in the specified Port
servidor.listen( svrPort );

var redisService = new Redis();

redisService.psubscribe('*', function(err, count) {});

redisService.on('pmessage', function(subscribed, channel, message) {
    message = JSON.parse(message);
    io.emit(channel + ':' + message.event, message.data);
});

/*
var app = require('express')();
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
*/
