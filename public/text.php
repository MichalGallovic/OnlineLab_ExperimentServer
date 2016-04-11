<!DOCTYPE HTML>
<?php
error_reporting(0);
session_start();
?>

<html>
<head>
    <title>Compile Arduino code and RPi Cam Preview</title>
    <script src="script_min.js"></script>
    <style>
       #narrow {
          float: right;
          /*width: 200px;*/
          /*background: lightblue;*/
        }
        #wide {
          float: left;
          /*width: calc(100% - 200px);*/
          /*background: lightgreen;*/
        } 
    </style>
</head>

<button onclick="window.location.href='picam/index.php'">Pi Cam</button>

<body style="background-color: rgb(225,225,225)" onload="setTimeout('init();', 100);">
    <hr style="background-color: rgb(150,150,150); color: rgb(150,150,150); width: 100%; height: 4px;">
    
    <div id="parent">
        <div id="wide">
            <form name="savefile" method="post" action="">
                <input type="hidden" name="filename" value="source"><br/>
                <textarea rows="16" cols="100" name="textdata">
void setup() {                
    // initialize the digital pin as an output.
    // Pin 13 has an LED connected on most Arduino boards:
    pinMode(13, OUTPUT);     
}

void loop() {
    digitalWrite(13, HIGH);   // set the LED on
    delay(1000);              // wait for a second
    digitalWrite(13, LOW);    // set the LED off
    delay(1000);              // wait for a second
}
                </textarea><br/>
                <input type="submit" name="submitsave" value="Send to Arduino">
            </form>
        </div>

        <div id="narrow">
            <img id="mjpeg_dest" />
        </div>
    </div>


<br/><hr style="background-color: rgb(150,150,150); color: rgb(150,150,150); width: 100%; height: 4px;"><br/>

<form name="openfile" method="post" action="">
    Open File: <input type="text" name="filename" value="source">
    <input type="submit" name="submitopen" value="Show file content">
</form>
<br/><hr style="background-color: rgb(150,150,150); color: rgb(150,150,150); width: 100%; height: 4px;"><br/>
File contents:<br/><br/>

<?php
if (isset($_POST)){
    if ($_POST['submitsave'] == "Send to Arduino"  && !empty($_POST['filename'])) {
        echo "commpiling done.";
        if(!file_exists($_POST['filename'] . ".c")){
            $file = tmpfile();
        }
        $file = fopen($_POST['filename'] . ".c","a+");
            // while(!feof($file)){
            //     $old = $old . fgets($file). "<br />";
            // }
        $text = $_POST["textdata"];
        file_put_contents($_POST['filename'] . ".c", $text);
        fclose($file);            

        $output = "<pre>".shell_exec("/var/www/olm_app_server/public/./testrunfromphp.sh 2>&1")."</pre>";
            //print_r($output);
    }

    if ($_POST['submitopen'] == "Show file content") {
        if(!file_exists($_POST['filename'] . ".c")){
            exit("Error: File does not exist.");
        }
        $file = fopen($_POST['filename'] . ".c", "r");
        while(!feof($file)){
            echo fgets($file). "<br />";
        }
        fclose($file);
    }
}
// include '/var/www/html/picam/index.php';
?>
<br/><hr style="background-color: rgb(150,150,150); color: rgb(150,150,150); width: 100%; height: 4px;"><br/>
</body>
</html>
