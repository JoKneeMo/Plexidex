var getDuration = function(millis){
millis = new Date(millis);
var hruntime = '';
hruntime += millis.getUTCHours() + " hr ";
hruntime += millis.getUTCMinutes() + " min";
return(hruntime);
var duration = hruntime;
};