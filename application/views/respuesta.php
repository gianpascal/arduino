<h3>Respuesta</h3>
<div id="respuesta" style="background-color: lightblue; height: 200px;"></div>
<script src="../../source/bower_components/jquery/dist/jquery.min.js"></script>
<script>
$(function () {
    var updateInterval = 1000 //Fetch data ever x milliseconds

    function update() {
        $.ajax({
            url: 'http://localhost/gabriel/index.php/pulso/leer',
            
            method: 'POST',
            success: function (data) {
                $('#respuesta').html(data);
            },
            error: function (xhr, status, bug) {
                console.log(status);
                console.log(xhr);
                console.log(bug);
            },
            complete: function (xhr, status) {
                //alert('Petición realizada');
                console.log(xhr);
                console.log(status);
            }
            });
        setTimeout(update, updateInterval)
    }
    update();
})
 </script>
