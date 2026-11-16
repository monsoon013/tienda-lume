const form = document.getElementById("rexistro");
const erros = document.getElementById("signupErrores");

form.addEventListener('submit', async(e) => {
    e.preventDefault();

    const data = {
        nome: document.getElementById("nome").value,
        apelidos: document.getElementById("apelidos").value,
        user: document.getElementById("userNameSign").value,
        psswd: document.getElementById("psswd").value, 
        email: document.getElementById("email").value,
    }

    try{
        const res = await fetch('api/signup.php', {
            method: 'POST',
            body: JSON.stringify(data)
        });
        const resposta = await res.json();
        if(res.ok){
            erros.classList.add('d-none');
            console.log('Usuario creado');
            form.reset();
        }else{
            erros.textContent = (resposta.errores || ['Error desconocido']).join(' . ');
            erros.classList.remove('d-none');
        }
    }catch (duendeSU){
        console.error(duendeSU);
    }
})