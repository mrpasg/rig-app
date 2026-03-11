<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "config.php";
?>
<script>

function calculateTotal(){

let operating = parseFloat(document.getElementById("operating").value) || 0;
let standby = parseFloat(document.getElementById("standby").value) || 0;
let breakdown = parseFloat(document.getElementById("breakdown").value) || 0;
let ilm = parseFloat(document.getElementById("ilm").value) || 0;
let zero = parseFloat(document.getElementById("zero").value) || 0;

let total = operating + standby + breakdown + ilm + zero;

let totalDisplay = document.getElementById("total");

totalDisplay.innerText = total.toFixed(1);

/* change color if exceeding 24 */

if(total > 24){
totalDisplay.style.color = "red";
}else{
totalDisplay.style.color = "#0d6efd";
}

}


/* prevent submit if > 24 */

document.querySelector("form").addEventListener("submit", function(e){

let operating = parseFloat(document.getElementById("operating").value) || 0;
let standby = parseFloat(document.getElementById("standby").value) || 0;
let breakdown = parseFloat(document.getElementById("breakdown").value) || 0;
let ilm = parseFloat(document.getElementById("ilm").value) || 0;
let zero = parseFloat(document.getElementById("zero").value) || 0;

let total = operating + standby + breakdown + ilm + zero;

if(total > 24){

alert("Total hours cannot exceed 24 hours.");

e.preventDefault();

}

});

</script>