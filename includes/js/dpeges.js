var DPEGES = function(diag, domID) {
    var detailsDiag = document.createElement("div");

    var smallDiag = document.createElement("div");
    smallDiag.id = domID+"Small";
    smallDiag.setAttribute("style", "font-size: 0; user-select: none;");    
    var styleBaseDiag = "padding-left : 7px; padding-right: 7px; padding-bottom: 5px; padding-top: 5px; font-size: 10px; ";
    
    var valueDiag = document.getElementById(domID).innerText;
    
    if(diag === "DPE") {
        var diagRanks = [
            { min: 0, max: 50, color: '#319834', textColor: '#000000', label: 'A' },
            { min: 51, max: 90, color: '#33cc31', textColor: '#000000', label: 'B' },
            { min: 91, max: 150, color: '#cbfc34', textColor: '#000000', label: 'C' },
            { min: 151, max: 230, color: '#fbfe06', textColor: '#000000', label: 'D' },
            { min: 231, max: 330, color: '#fbcc05', textColor: '#000000', label: 'E' },
            { min: 331, max: 450, color: '#fc9935', textColor: '#000000', label: 'F' },
            { min: 451, max: 500, color: '#fc0205', textColor: '#ffffff', label: 'G' }
        ];
    }else{
        var diagRanks = [
            { min: 0, max: 5, color: '#f2eff4', textColor: '#000000', label: 'A' },
            { min: 6, max: 10, color: '#dfc1f7', textColor: '#000000', label: 'B' },
            { min: 11, max: 20, color: '#d6aaf4', textColor: '#000000', label: 'C' },
            { min: 21, max: 35, color: '#cc93f4', textColor: '#000000', label: 'D' },
            { min: 36, max: 55, color: '#bb72f3', textColor: '#ffffff', label: 'E' },
            { min: 56, max: 80, color: '#a94cee', textColor: '#ffffff', label: 'F' },
            { min: 81, max: 100, color: '#8b1ae1', textColor: '#ffffff', label: 'G' }
        ];
    }
    
    diagRanks.forEach(rank => {
        var span = document.createElement("span");
        span.textContent = rank.label;
        span.setAttribute("style", styleBaseDiag+"color: "+rank.textColor + "; background-color: "+rank.color);
        if(valueDiag >= rank.min && valueDiag <= rank.max) {
            span.style.fontWeight = "bold";
            span.style.border = "white solid";
            span.style.fontSize = "15px";
        } 
        smallDiag.appendChild(span);              
    });
            
    

    //document.getElementById(domID).textContent = '';
    document.getElementById(domID).appendChild(smallDiag);
};

DPEGES("DPE", "DPEValue");
DPEGES("GES", "GESValue");