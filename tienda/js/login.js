

const formLog = document.getElementById("login");
const errosLog = document.getElementById("loginErrores");

formLog.addEventListener('submit', async(e) => {
    e.preventDefault();

    const data = {
        user: document.getElementById("userNameLog").value,
        pass: document.getElementById("psswdLog").value, 
    }

    try{
        const res = await fetch('api/login.php', {
            method: 'POST',
            body: JSON.stringify(data)
        });
        const resposta = await res.json();
        if(res.ok){
            window.location.href = 'sesion.php';
        }else{
            errosLog.textContent = (resposta.errores || ['Error desconocido']).join(' . ');
            errosLog.classList.remove('d-none');
        }
    }catch (duendeLog){
        console.error(duendeLog);
    }
})