const contain = document.getElementById('productos');
const aviso = document.getElementById('avisoTienda');

const eur = new Intl.NumberFormat('es-Es', {style: 'currency', currency: 'EUR'});

function createProduct(p){
    const card = document.createElement('article');
    card.dataset.id = p.id;

    if(p.imagen) {
        const img = document.createElement('img');
        img.src = p.imagen;
        img.alt = p.nombre;
        img.width = 200;
        card.appendChild(img);
    }

    const title = document.createElement('h3');
    title.textContent = p.nombre;

    const desc = document.createElement('p');
    desc.textContent = p.descripcion;

    const precio = document.createElement('p');
    precio.textContent = eur.format(p.precio);

    const stock = document.createElement('p');
    stock.textContent = p.stock > 0 ? `Stock: ${p.stock}` : 'Agotado';

    const btn = document.createElement('button');
    btn.textContent = 'Añadir';
    btn.className = 'btn-anadir';
    btn.dataset.id = p.id;
    btn.dataset.nombre = p.nombre;
    btn.disabled = p.stock <= 0;

    card.append(title, desc, precio, stock, btn);
    if(esAdmin) card.append(adminControls(p));
    return card;
}

async function loadProds(){
    try{
        const res = await fetch('api/productos.php');
        const data = await res.json();
        if(!res.ok){
            throw new Error((data.erros || ['Error desconocido']).join(''));
        }
        esAdmin = data.admin === true;
        contain.replaceChildren(...data.produtos.map(createProduct));
        if(esAdmin) renderAdminPanel();

        contain.replaceChildren(...data.produtos.map(createProduct));
        aviso.textContent = data.produtos.length ? '' : 'Todavía no hay productos';
    } catch (err){
        console.error(err);
        aviso.textContent = 'No se pudieron cargar los productos';
    }
}

loadProds();