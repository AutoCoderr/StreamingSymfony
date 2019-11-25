function remplace(str,A,B) {
    while(str.replace(A,B) != str){
        str = str.replace(A,B);
    }
    return str;
}

function toMin(word) {
    const maj = ["A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z"];
    const min = ["a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z"];
    for (let i=0;i<maj.length;i++) {
        word = remplace(word,maj[i],min[i]);
    }
    return word;
}

function copyObj(obj) {
    if (obj instanceof Array) {
        return copyList(obj);
    } else {
        return copyDict(obj);
    }
}

function copyDict(dict) {
    let copy = {};
    for (let key in dict) {
        if (typeof(dict[key]) == "object") {
            copy[key] = copyObj(dict[key]);
        } else {
            copy[key] = dict[key];
        }
    }
    return copy;
}

function copyList(list) {
    let copy = [];
    for (let i=0;i<list.length;i++) {
        if (typeof(list[i]) == "object") {
            copy.push(copyObj(list[i]));
        } else {
            copy.push(list[i]);
        }
    }
    return copy
}