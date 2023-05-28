//Fonction attente
function sleep(ms) {
    return new Promise(
        resolve => setTimeout(resolve, ms)
    );
}

//Verifier si un element est visible
function isInViewport(element) {
    const rect = element.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}


//Fonction Diapo
function Diapofunct(Diapo, timer, id) {
    //On met au diapo à 0
    var iElement = 0;

    //Temps entre chaque image en ms
    //const timer = 1500;

    //Initialisation numDiapo
    const numDiapo = {};

    //On hover
    var onHover = 0;

    //On revient tout en haut
    //Diapo = document.getElementById('Diapo');
    var lenghtElement = Diapo.getElementsByTagName('img').length;
    //Debug
    //console.log(`Id : ${id} | Longueur : ${lenghtElement}`);

    Diapo.getElementsByClassName('medias')[0].scrollTo({
        top: 0,
        left: 0,
        behavior: 'smooth'
    });

    //Boutton
    // On récupère les deux flèches
    var next = Diapo.querySelector("#gauche");
    var prev = Diapo.querySelector("#droite");

    // On met en place les écouteurs d'évènements sur les flèches
    next.addEventListener("click", function () { numDiapo.var -= 1 });
    prev.addEventListener("click", function () { numDiapo.var += 1 });

    //Avancement
    var avancement = Diapo.getElementsByClassName('avancement')[0];
    avancement.innerText = ` ${iElement + 1}/${lenghtElement}`;

    // On gère l'arret et la reprise du diapo
    Diapo.addEventListener("mouseover", function () { onHover = 1 });
    Diapo.addEventListener("mouseout", function () { onHover = 0 });

    //Changement variable numDiapo
    Object.defineProperty(numDiapo, 'var', {
        get: function () { return iElement; },
        set: function (i) {
            //Debug
            //console.log(`Id : ${id} | Photos : ${i}`);
            iElement = i;
            Diapo.getElementsByClassName('medias')[0].scrollTo({
                top: 0,
                left: Diapo.offsetWidth * i,
                behavior: 'smooth'
            });

            if (numDiapo.var >= lenghtElement) { numDiapo.var = 0; }
            if (numDiapo.var < 0) { numDiapo.var = lenghtElement - 1; }

            avancement.innerText = ` ${iElement + 1}/${lenghtElement}`;
        }
    });

    //Fonction pour swipe les diapos
    Diapofunct.prototype.swipe = async function () {
        await sleep(timer);
        while (true) {
            if (!onHover && isInViewport(Diapo)) {
                numDiapo.var += 1;
            }
            await sleep(timer);
        }
    }

    //Debug
    //console.log(this);

    //On lance la fontion swipe
    Diapofunct.prototype.swipe();
}

