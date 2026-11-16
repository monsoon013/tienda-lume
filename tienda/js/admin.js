let esAdmin = false;

async function apiAdmin(url, cuerpo){
    try{
        const res = await fetch(url, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(cuerpo),
        });
        if(res.status === 401){
            window.location.href = 'sesion.php';
            return false;
        }
        const data = await res.json().catch(() => ({}));
        if(!res.ok){
            showMess(textErr(data));
            return false;
        }

        showMess('');
        loadProds();
        return true;
    }catch(duendeAdmin){
        console.error(duendeAdmin);
        showMess('Error de conexión');
        return false;
    }
}

function adminControls(p){
    const box = document.createElement('div');

    const btnEditar = document.createElement('button');
    btnEditar.textContent = 'Editar';
    btnEditar.addEventListener('click', () => editProd(p));

    const btnStock = document.createElement('button');
    btnStock.textContent = 'Ajustar stock';
    btnStock.addEventListener('click', () => ajustarStock(p));

    const btnBorrar = document.createElement('button');
    btnBorrar.textContent = 'Eliminar';
    btnBorrar.addEventListener('click', () => remProd(p));

    box.append(btnEditar, btnStock, btnBorrar);
    return box;
}

function editProd(p){
    const nombre = prompt('Nombre: ', p.nombre );
    if(nombre === null ) return;
    const descripcion = prompt('Descripción: ', p.descripcion);
    if(descripcion === null) return;
    const precio = prompt('Precio: ', p.precio);
    if(precio === null) return;

    apiAdmin('api/admin/editar-prod.php', {
        id: p.id,
        nombre: nombre,
        descripcion: descripcion,
        precio: Number(precio.replace(',', '.')),
    });
}

function remProd(p){
    if(!confirm('¿Eliminar "' + p.nombre + '"?')) return;
    apiAdmin('api/admin/elim-prod.php', {id: p.id});
}

function ajustarStock(p){
    const txt = prompt('Ajuste de stock para "' + p.nombre + '" : ');
    if(txt === null) return;
    const ajuste = Number(txt);
    if(!Number.isInteger(ajuste) || ajuste === 0){
        showMess('El ajuste tiene que ser un entero distinto de 0');
        return;
    }
    apiAdmin('api/admin/act-stock.php', {id: p.id, ajuste: ajuste});
}

function renderAdminPanel(){
    if(document.getElementById('admin-panel'))return;

    const panel = document.createElement('div');
    panel.id = 'admin-panel';
    const title = document.createElement('h2');
    title.textContent = 'Nuevo Producto';
    panel.appendChild(title);

    function campo(etiqueta, tipo){
        const label = document.createElement('label');
        label.textContent = etiqueta + ' ';
        const input = document.createElement('input');
        input.type = tipo;
        if (tipo === 'number') input.stop = 'any';
        label.appendChild(input);
        panel.append(label, document.createElement('br'));
        return input;
    }

    const nombre = campo('Nombre', 'text');
    const descripcion = campo('Descripción', 'text');
    const precio = campo('Precio', 'number');
    const stock = campo('Stock', 'number');
    const imagen = campo('URL de imagen (opcional)', 'url');

    const btn = document.createElement('button');
    btn.textContent = 'Crear';
    btn.addEventListener('click', async() => {
        const ok = await apiAdmin('api/admin/crear-prod.php', {
            nombre: nombre.value,
            descripcion: descripcion.value,
            precio: Number(precio.value),
            stock: Number(stock.value),
            imagen: imagen.value || null,
        });

        if(ok){
            [nombre, descripcion, precio, stock, imagen].forEach((i) =>(i.value = ''));
        }
    })
    panel.appendChild(btn);
    document.getElementById('productos').before(panel);
}

