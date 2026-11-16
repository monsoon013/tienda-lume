let carrito = [];

const MAX_PROD = 99;
const URL_COMPRA = 'api/comprar.php';
const URL_LOGIN = 'sesion.php';
const cont_carrito = document.getElementById('carrito');
const btnComprar = document.getElementById('btn-comprar');
const mess = document.getElementById('mensaje');

document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-anadir');
    if(btn){
        addProds(btn.dataset.id, btn.dataset.nombre);
    }
    const rem = e.target.closest('.btn-remove');
    if(rem) removeProd(rem.dataset.id);
})

btnComprar.addEventListener('click', comprar);

function showMess(texto){
    mess.textContent = texto;
}

function addProds(id, nombre){
    const linea = carrito.find((lin) => lin.id === id);
    if(linea) {
        if (linea.cantidad >= MAX_PROD){
            showMess('Max: ' + MAX_PROD + " unidades por producto");
            return;
        }
        linea.cantidad += 1;
    } else{
        carrito.push({id: id, nombre: nombre, cantidad: 1});
    }
    showMess('');
    renderCarrito();
}

function removeProd(id){
    const linea = carrito.find((lin) => lin.id === id);
    if(!linea) return;

    linea.cantidad -= 1;
    if(linea.cantidad <= 0){
        carrito = carrito.filter((lin) => lin.id !== id);
    }
    renderCarrito();
}

function renderCarrito(){
    cont_carrito.replaceChildren();
    if(carrito.length === 0){
        cont_carrito.textContent = 'El carrito está vacío';
        btnComprar.disabled = true;
        return;
    }

    btnComprar.disabled = false;

    carrito.forEach((p) =>{
        const fila = document.createElement('div');
        const text = document.createElement('span');
        text.textContent = p.nombre + ' - ' + p.cantidad;

        const rem = document.createElement('button');
        rem.textContent = 'Quitar 1';
        rem.className = 'btn-remove';
        rem.dataset.id = p.id;

        fila.append(text, rem);
        cont_carrito.appendChild(fila);
    })

}

function textErr(data){
    if(Array.isArray(data.erros) && data.erros.length) return data.erros.join(' ');
    if (typeof data.erros === 'string') return data.erros;
    return data.error || data.mensaje || 'No se puede completar la compra';
}

async function comprar(){
    if (carrito.length === 0) return;

    btnComprar.disabled = true; //evita doble click

    const lineas = carrito.map((p) => ({id: p.id, cantidad:Number(p.cantidad)}));

    try{
        const res = await fetch(URL_COMPRA, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json'},
            body: JSON.stringify({lineas: lineas}),
        });

        if(res.status === 401){
            window.location.href = URL_LOGIN;
            return;
        }

        const data = await res.json().catch(() => ({})); //objeto vacío
        if(res.status === 201){
            carrito = [];
            renderCarrito();
            showMess('Compra realizada. Total: ' + data.total);

            if (typeof loadProds === 'function') loadProds(); //refrescar los productos para que se vea el stock actualizado
        }else {
            showMess(textErr(data));
            btnComprar.disabled = false;
        }
    }catch (duende){
        console.error(duende);
        showMess('Error de conexión, intente de nuevo');
        btnComprar.disabled = false;
    }
}

renderCarrito();
