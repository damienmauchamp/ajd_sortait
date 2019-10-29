<?php

include __DIR__ . '/config.php';

header("Content-type:text/html");

$date = isset($_GET["date"]) ? date("2019-m-d", strtotime($_GET["date"])) : date("Y-m-d");

?>
<script src="http://code.jquery.com/jquery-2.2.4.js"></script>
<div id="main-get">
    <label for="date">Date :</label>
    <input type="date" id="date" name="date"
    value="<?= $date ?>"
    min="<?= date("Y") ?>-01-01" max="<?= date("Y") ?>-12-31">
</div>

<h2>On this day</h2>
<pre class="results" id="result-today"></pre>

<h3>This month (date unknown)</h3>
<pre class="results" id="result-this-month"></pre>

<h3>Autres</h3>
<pre class="results" id="result-unknown"></pre>

<h3>À venir</h3>
<pre class="results" id="result-upcoming"></pre>

<script>
    var albums = [];
    var anneeStart = 1984;
    date = new Date();
    $("#date").on("input", function(e, init = false) {
        date = new Date($(this).val());

        if (!init)
            updateURL(date_toString(date));

        var anneeEnd = date.getFullYear();
        if (albums.length) {
            setResults();
            return;
        }

        $.getJSON('data/albums.json', function(data) {
            $.each( data, function( key, val ) {
                albums[key] = val;
            });
        });

        /*
        $("#result-today").text("Loading...");
        for (var year = anneeStart ; year <= anneeEnd ; year++) {
            if (1985 <= year && year < 1990)
                continue;

            $.ajax({
                url: "../releases/ajax.php",
                method: "GET",
                data: { year: year },
                success: function(response) {
                    console.log(response);
                    albums[Object.keys(response)] = response;
                }
            });
        }*/

        $(document).ajaxStop(function() {
            console.log(albums);
            setResults();
        });
    }).trigger("input", true);

    function setResults() {
        $(".results").text("");
        albums.forEach(function(items, key) {
            console.log(Object.values(items));
            displayToday(Object.values(items).filter(album => parseInt(album.day) == date.getDate() && parseInt(album.month) == date.getMonth()+1));
            displayThisMonth(Object.values(items).filter(album => album.day == "" && parseInt(album.month) == date.getMonth()+1));
            displayUnknown(Object.values(items).filter(album => album.date === 3));
        });
    }
    
    function displayToday(albums) {
        albums.forEach(function(item) {
            $("#result-today").append(item.year + ": " + item.artist + " - " + item.album + "\n");
        });
    }

    function displayThisMonth(albums) {
        albums.forEach(function(item) {
            $("#result-this-month").append(item.year + ": " + item.artist + " - " + item.album + "\n");
        });
    }

    function displayUnknown(albums) {
        albums.forEach(function(item) {;
            var res = "";
            if (item.start.day !== "" || item.start.month !== "" || item.end.day !== "" || item.end.month !== "") {
                var intervalle = (item.start.day ? item.start.day : "??") + "/" + (item.start.month ? item.start.month : "??") + " ==> " + (item.end.day ? item.end.day : "??") + "/" + (item.end.month ? item.end.month : "??");
                res = item.year + " (" + intervalle + ") : " + item.artist + " - " + item.album + "\n";
                $("#result-unknown").append(res);
            } else {
                res =  item.year + ": " + item.artist + " - " + item.album + "\n";

                if (parseInt(item.year) === new Date().getFullYear())
                    $("#result-upcoming").append(res);
                else
                    $("#result-unknown").append(res);
            }
        });
    }

    function updateURL(date) {
        var isToday = new Date(date).setHours(0,0,0,0) === new Date().setHours(0,0,0,0);
        if (history.pushState) {
            var newurl = window.location.protocol + "//" + window.location.host + window.location.pathname + (isToday ? '' : '?date=' + date);
            window.history.pushState({path:newurl},'',newurl);
        }
    }

    function twoDigitsNumber(n) {
        return ("0" + n).slice(-2);
    }

    function date_toString(date) {
        return date.getFullYear() + "-" + twoDigitsNumber(date.getMonth() + 1) + "-" + twoDigitsNumber(date.getDate());
    }
</script>