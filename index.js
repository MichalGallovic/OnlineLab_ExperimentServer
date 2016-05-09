var server = require('http').Server();
var io = require('socket.io')(server);
var fs = require('fs');
var Redis = require('ioredis');
var redis = new Redis();

Array.prototype.clean = function(deleteValue) {
  for (var i = 0; i < this.length; i++) {
    if (this[i] == deleteValue) {         
      this.splice(i, 1);
      i--;
    }
  }
  return this;
};


function parseFile(path) {
  var fileArray = fs.readFileSync(path).toString().split('\n');
  var experimentSetup = fileArray.slice(0,9);

  // dorobit, aby parsovalo az od === znacky
  fileArray = fileArray.slice(10,fileArray.length-1);
  fileArray = fileArray.map(function(line) {
    return line.split(',');
  }).clean("");

  var rotatedArray = [];

  fileArray.forEach(function(lineArr) {
    lineArr.forEach(function(measured_val, index) {
      if(rotatedArray[index]) {
        rotatedArray[index].push(measured_val);
      } else {
        rotatedArray.push([measured_val]);
      }
    });
  });


  return {
    data: rotatedArray,
    settings: {
      instance: experimentSetup[2]
    }
  };
}

function streamDataOfFile(path, user_id) {
  var data = parseFile(path);
  io.emit('experiment-data:' + user_id, data); 
  console.log('experiment-data:'+user_id);
}

redis.subscribe('experiment-channel');

var streamingId = -1;

redis.on('message', function(channel, message) {
  var message = JSON.parse(message);
  console.log(message);
  if(message.event == 'ExperimentStarted') {
    streamingId = setInterval(function() {
      streamDataOfFile(message.data.file_path, message.data.user_id);
    }, 200);
  } else if(message.event == 'ExperimentFinished') {
    clearInterval(streamingId);
  }
});

server.listen(3000);

