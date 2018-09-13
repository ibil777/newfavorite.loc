d = new Array("воскресенье","понедельник","вторник","среда","четверг","пятница","суббота");
m = new Array(" января"," февраля"," марта"," апреля"," мая"," июня"," июля"," августа"," сентября"," октября"," ноября"," декабря");

today = new Date();
day = today.getDate();
year = today.getYear();

if (year < 2000)
year = year + 1900;

end = "";
if (day==1 || day==21 || day==31) end="";
if (day==2 || day==22) end="";
if (day==3 || day==23) end="";
day+=end;

document.write(" "+day+" "+ m[today.getMonth()]+" " + year + " года, ");
document.write(d[today.getDay()]);