function startCalc(){
interval = setInterval("calc()",1);
}
function calc(){
one = document.SumForm.firstBox.value;
two = document.SumForm.secondBox.value;
document.SumForm.thirdBox.value = (one * 1) + (two * 1);
}
function stopCalc(){
clearInterval(interval);
}



function startCalcNS(){
intervalNS = setInterval("calcNS()",500);
}

function calcNS(){
one = document.southform.south.value;
two = document.northform.north.value;
document.northsouthform.northsouth.value = (one * 1.0) + (two * 1.0);

var xmlhttp = new XMLHttpRequest();
xmlhttp.open("GET", "sys/set_vals.php?" + "north_south" + "=" + document.northform.north.value + ";" + document.southform.south.value , true);
xmlhttp.send()

}

function stopCalcNS(){
clearInterval(intervalNS);
}

function startCalcEW(){
intervalEW = setInterval("calcEW()",500);
}

function calcEW(){
one = document.westform.west.value;
two = document.eastform.east.value;
document.eastwestform.eastwest.value = (one * 1.0) + (two * 1.0);

var xmlhttp = new XMLHttpRequest();
xmlhttp.open("GET", "sys/set_vals.php?" + "east_west" + "=" + document.eastform.east.value + ";" + document.westform.west.value , true);
xmlhttp.send()

}

function stopCalcEW(){
clearInterval(intervalEW);
}
