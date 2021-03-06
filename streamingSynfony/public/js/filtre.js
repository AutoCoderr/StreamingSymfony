function ABiggerThanB(A, B) {
    let n = 0;
    while (letterToNum[A[n]] === letterToNum[B[n]] && n < A.length && n<B.length) {
        n += 1;
    }
    if (n === A.length || n === B.length) {
        if (A.length > B.length) {
            return true;
        } else {
            return false;
        }
    }
    if (letterToNum[A[n]] > letterToNum[B[n]]) {
        return true;
    } else {
        return false;
    }
}

function ASmallerThanB(A, B) {
    let n = 0;
    while (letterToNum[A[n]] === letterToNum[B[n]] && n < A.length && n<B.length) {
        n += 1;
    }
    if (n === A.length || n === B.length) {
        if (A.length < B.length) {
            return true;
        } else {
            return false;
        }
    }
    if (letterToNum[A[n]] < letterToNum[B[n]]) {
        return true;
    } else {
        return false;
    }
}

const letterToNum = {
    "0": -9,
    "1": -8,
    "2": -7,
    "3": -6,
    "4": -5,
    "5": -4,
    "6": -3,
    "7": -2,
    "8": -1,
    "9": 0,
    "a": 1,
    "à": 1,
    "â": 1,
    "ä": 1,
    "A": 1,
    "Â": 1,
    "Ä": 1,
    "b": 2,
    "B": 2,
    "c": 3,
    "C": 3,
    "d": 4,
    "D": 4,
    "e": 5,
    "é": 5,
    "è": 5,
    "ê": 5,
    "ë": 5,
    "E": 5,
    "Ê": 5,
    "Ë": 5,
    "f": 6,
    "F": 6,
    "g": 7,
    "G": 7,
    "h": 8,
    "H": 8,
    "i": 9,
    "î": 9,
    "ï": 9,
    "I": 9,
    "Î": 9,
    "Ï": 9,
    "j": 10,
    "J": 10,
    "k": 11,
    "K": 11,
    "l": 12,
    "L": 12,
    "m": 13,
    "M": 13,
    "n": 14,
    "N": 14,
    "o": 15,
    "ô": 15,
    "ö": 15,
    "O": 15,
    "Ô": 15,
    "Ö": 15,
    "p": 16,
    "P": 16,
    "q": 17,
    "Q": 17,
    "r": 18,
    "R": 18,
    "s": 19,
    "S": 19,
    "t": 20,
    "T": 20,
    "u": 21,
    'ù': 21,
    'û': 21,
    "ü": 21,
    "U": 21,
    "Û": 21,
    "Ü": 21,
    "v": 22,
    "V": 22,
    "w": 23,
    "W": 23,
    "x": 24,
    "X": 24,
    "y": 25,
    "Y": 25,
    "z": 26,
    "Z": 26
};


function filtre() {
    let listed = filtreCategorie(liste);
    listed = filtreNom(listed);
    listed = filtreType(listed);
    listed = filtreEmail(listed);
    listed = filtreUploader(listed);
    listed = filtreTrie(listed);
    display(listed);
}

function filtreUploader(liste) {
    const idUploader = parseInt($("#filtreUploader").val());
    if (idUploader == "" | isNaN(idUploader)) {
        return liste;
    } else {
        let listed = [];
        for (let i=0;i<liste.length;i++) {
            if (liste[i].idUploader === idUploader) {
                listed.push(liste[i]);
            }
        }
        return listed;
    }
}

function filtreEmail(liste) {
    const email = $("#filtreEmail").val();
    if (email == "" | typeof(email) == "undefined") {
        return liste;
    } else {
        let listed = [];
        for (let i=0;i<liste.length;i++) {
            if (toMin(liste[i].email).replace(toMin(email),"") != toMin(liste[i].email)) {
                listed.push(liste[i]);
            }
        }
        return listed;
    }
}

function filtreCategorie(liste) {
    const categorie = $("#filtreCategorie").val();
    if (categorie == "" | typeof(categorie) == "undefined") {
        return liste;
    } else {
        let listed = [];
        for (let i=0;i<liste.length;i++) {
            if (liste[i].categorie == categorie) {
                listed.push(liste[i]);
            }
        }
        return listed;
    }
}

function filtreNom(liste, nom = $("#filtreNom").val()) {
    if (nom == "" | typeof(nom) == "undefined") {
        return liste;
    } else {
        let listed = [];
        for (let i=0;i<liste.length;i++) {
            if (toMin(liste[i].titre).replace(toMin(nom),"") != toMin(liste[i].titre)) {
                listed.push(liste[i]);
            }
        }
        return listed;
    }
}

function filtreType(liste) {
    const filtreType = $("#filtreType").val();
    if (filtreType == "" | typeof(filtreType) == "undefined") {
        return liste;
    } else {
        let listed = [];
        for (let i=0;i<liste.length;i++) {
            if (liste[i].type == filtreType) {
                listed.push(liste[i]);
            }
        }
        return listed;
    }
}

function filtreTrie(listA) {
    let liste = copyObj(listA);
    const filtreTrie = $("#filtreTrie").val();
    if (filtreTrie == "" | filtreTrie == "alphaCroissant" | typeof(filtreTrie) == "undefined") {
        return liste;
    } else {
        switch(filtreTrie) {
            /*case "alphaCroissant":
                return trieAlphabetique("croissant", listFilm);*/
            case "alphaDecroissant":
                return trieAlphabetique("decroissant", liste);
        }
    }
}


function trieAlphabetique(ordre, liste) {
    let tried = false;
    while (!tried) {
        tried = true;
        for (let i=0;i<liste.length;i++) {
            if (i < liste.length-1) {
                if (ordre == "croissant" & ABiggerThanB(liste[i].titre,liste[i+1].titre) |
                    ordre == "decroissant" & ASmallerThanB(liste[i].titre,liste[i+1].titre)) {
                    const tmp = copyObj(liste[i+1]);
                    liste[i+1] =  copyObj(liste[i]);
                    liste[i] = tmp;
                    tried = false;
                }
            }
        }
    }
    return liste;
}

function filtrePopu(liste,sens) {
    let tried = false;
    while (!tried) {
        tried = true;
        for (let i=0;i<liste.length;i++) {
            if (i < liste.length-1) {
                if (sens == "popinv" & liste[i].scorePop > liste[i+1].scorePop |
                    sens == "pop" & liste[i].scorePop < liste[i+1].scorePop) {
                    const tmp = copyObj(liste[i+1]);
                    liste[i+1] =  copyObj(liste[i]);
                    liste[i] = tmp;
                    tried = false;
                }
            }
        }
    }
    return liste;
}