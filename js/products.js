// Fichier JavaScript pour gérer l'affichage des produits et les interactions utilisateur

document.addEventListener('DOMContentLoaded', function() {
    // Initialiser les fonctionnalités des cartes de produits
    initProductCards();
    
    // Initialiser la pagination
    initPagination();
    
    // Initialiser le filtre de recherche
    initSearchFilter();
});

/**
 * Initialise les fonctionnalités des cartes de produits
 */
function initProductCards() {
    // Animation au survol des cartes (déjà gérée en CSS)
    
    // Gestion des clics sur les boutons "Voir détails"
    const detailButtons = document.querySelectorAll('.card .btn-primary');
    detailButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            // Si le bouton est un lien, laisser le comportement par défaut
            if (this.tagName.toLowerCase() === 'a') {
                return;
            }
            
            // Sinon, empêcher le comportement par défaut et rediriger
            e.preventDefault();
            const productId = this.getAttribute('data-product-id');
            if (productId) {
                window.location.href = `product.php?id=${productId}`;
            }
        });
    });
}

/**
 * Initialise la pagination
 */
function initPagination() {
    const paginationLinks = document.querySelectorAll('.pagination .page-link');
    paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Si le lien a un attribut href, laisser le comportement par défaut
            if (this.getAttribute('href') && this.getAttribute('href') !== '#') {
                return;
            }
            
            // Sinon, empêcher le comportement par défaut et charger la page
            e.preventDefault();
            const page = this.getAttribute('data-page');
            if (page) {
                loadMoreProducts(page);
            }
        });
    });
}

/**
 * Initialise le filtre de recherche
 */
function initSearchFilter() {
    const searchForm = document.querySelector('form[action="search.php"]');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const searchInput = this.querySelector('input[name="q"]');
            if (!searchInput.value.trim()) {
                e.preventDefault();
                alert('Veuillez entrer un terme de recherche');
            }
        });
    }
}

/**
 * Crée une carte de produit à partir des données
 * @param {Object} product Données du produit
 * @return {HTMLElement} Élément DOM de la carte
 */
function createProductCard(product) {
    const card = document.createElement('div');
    card.className = 'col-md-4 mb-4';
    card.innerHTML = `
        <div class="card h-100">
            <img src="${escapeHtml(product.image)}" class="card-img-top" alt="${escapeHtml(product.name)}">
            <div class="card-body">
                <h5 class="card-title">${escapeHtml(product.name)}</h5>
                <p class="card-text">${escapeHtml(product.description.substring(0, 100))}...</p>
                <p class="card-text"><strong>Catégorie:</strong> ${escapeHtml(product.category)}</p>
                <p class="card-text"><strong>Prix:</strong> ${formatPrice(product.price)} €</p>
                <p class="card-text"><strong>Stock:</strong> ${product.stock} unités</p>
                <a href="product.php?id=${escapeHtml(product.id)}" class="btn btn-primary">Voir détails</a>
            </div>
        </div>
    `;
    return card;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatPrice(price) {
    return Number(price).toLocaleString('fr-FR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function updatePagination(currentPage, totalPages) {
    const pagination = document.createElement('div');
    pagination.className = 'col-12';
    pagination.innerHTML = `
        <nav aria-label="Navigation des pages">
            <ul class="pagination justify-content-center">
                <li class="page-item ${currentPage <= 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${currentPage - 1}">Précédent</a>
                </li>
                ${Array.from({length: totalPages}, (_, i) => i + 1)
                    .map(i => `
                        <li class="page-item ${i === currentPage ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                        </li>
                    `).join('')}
                <li class="page-item ${currentPage >= totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${currentPage + 1}">Suivant</a>
                </li>
            </ul>
        </nav>
    `;
    return pagination;
}

function showError(message) {
    const container = document.getElementById('products-container');
    container.innerHTML = `
        <div class="col-12 text-center">
            <div class="alert alert-warning">
                <h4>Erreur de chargement</h4>
                <p>${escapeHtml(message)}</p>
                ${message === 'Configuration API manquante' ? 
                    '<p>Veuillez modifier le fichier <code>includes/config.php</code> et remplacer les valeurs par défaut par vos informations d\'API.</p>' : 
                    ''}
            </div>
        </div>
    `;
}

function loadMoreProducts(page) {
    const container = document.getElementById('products-container');
    container.innerHTML = '<div class="col-12 text-center"><div class="spinner-border" role="status"><span class="sr-only">Chargement...</span></div></div>';

    fetch(`get_products.php?page=${page}&limit=12`)
        .then(response => response.json())
        .then(data => {
            container.innerHTML = '';
            
            if (!data.success) {
                showError(data.error || 'Erreur lors du chargement des produits');
                return;
            }
            
            if (!data.data.products || data.data.products.length === 0) {
                container.innerHTML = '<div class="col-12 text-center"><p>Aucun produit disponible pour le moment.</p></div>';
                return;
            }
            
            // Ajouter les cartes de produits
            data.data.products.forEach(product => {
                container.appendChild(createProductCard(product));
            });
            
            // Ajouter la pagination si nécessaire
            if (data.data.pagination.totalPages > 1) {
                container.appendChild(
                    updatePagination(
                        data.data.pagination.currentPage,
                        data.data.pagination.totalPages
                    )
                );
                initPagination();
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showError('Une erreur est survenue lors de la communication avec le serveur');
        });
}