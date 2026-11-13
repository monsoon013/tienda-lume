
const bcrypt = require("bcryptjs");

const saltRounds = 10;
const psswd = document.getElementById("psswd").value;
const passwordHash = await bcrypt.hash(psswd, saltRounds);

let data = {
    nome: document.getElementById("nome").value,
    apelidos: document.getElementById("apelidos").value,
    user: document.getElementById("userNameSign").value,
    psswd: passwordHash, 
    email: document.getElementById("email").value,

}

async function saveData () {
    try{
        const res = await fetch('validacion-form.php', {
            method: 'POST',
            body: JSON.stringify(data)
        });
        return res.ok;
    }catch(duendeFetch){
        return false; 
    }
}