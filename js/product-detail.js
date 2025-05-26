// Fichier JavaScript pour la page de détail

document.addEventListener('DOMContentLoaded', function() {
    // Initialiser le carousel
    initCarousel();
    
    // Charger les produits similaires
    loadSimilarProducts();
    
    // Initialiser les boutons d'action
    initActionButtons();
});

/**
 * Initialise le carousel d'images
 */
function initCarousel() {
    // Si Bootstrap 5 est utilisé, le carousel est déjà initialisé automatiquement
    // Cette fonction peut être utilisée pour des personnalisations supplémentaires
}

/**
 * Charge les produits similaires
 */
function loadSimilarProducts() {
    // Récupérer l'ID du produit actuel depuis l'URL
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('id');
    
    if (!productId) return;
    
    // Appel AJAX pour récupérer les produits similaires
    fetch(`ajax/get_similar_products.php?id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.products && data.products.length > 0) {
                const container = document.getElementById('similar-products');
                container.innerHTML = ''; // Vider le conteneur
                
                // Ajouter les produits similaires au conteneur
                data.products.forEach(product => {
                    const productCard = createProductCard(product);
                    container.appendChild(productCard);
                });
            } else {
                // Afficher un message amélioré avec des suggestions alternatives
                document.getElementById('similar-products').innerHTML = `
                    <div class="col-12">
                        <div class="empty-similar-products p-4 my-3 rounded">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h4 class="mb-3">Aucun produit similaire disponible pour le moment</h4>
                                    <p>Nous n'avons pas encore de produits similaires à vous proposer, mais voici quelques suggestions qui pourraient vous intéresser :</p>
                                    <div class="mt-3">
                                        <a href="products.php" class="btn btn-outline-primary me-2">Découvrir tous nos produits</a>
                                        <a href="categories.php" class="btn btn-outline-secondary">Explorer les catégories</a>
                                    </div>
                                </div>
                                <div class="col-md-4 text-center">
                                    <div class="empty-state-icon">
                                        <i class="bi bi-search" style="font-size: 4rem; opacity: 0.6;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Ajouter une animation simple
                setTimeout(() => {
                    const emptyState = document.querySelector('.empty-similar-products');
                    if (emptyState) {
                        emptyState.classList.add('fade-in');
                    }
                }, 100);
            }
        })
        .catch(error => {
            console.error('Erreur lors du chargement des produits similaires:', error);
            document.getElementById('similar-products').innerHTML = '<p class="col-12 text-center">Impossible de charger les produits similaires.</p>';
        });
}

/**
 * Initialise les boutons d'action
 */
function initActionButtons() {
    // Bouton Ajouter au panier
    const addToCartBtn = document.querySelector('.btn-primary.btn-lg');
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function() {
            // Récupérer l'ID du produit
            const urlParams = new URLSearchParams(window.location.search);
            const productId = urlParams.get('id');
            
            // Simuler l'ajout au panier (à remplacer par un vrai appel AJAX)
            alert('Produit ajouté au panier !');
            
            // Dans une vraie implémentation, on ferait un appel AJAX
            // fetch('ajax/add_to_cart.php', {
            //     method: 'POST',
            //     headers: {
            //         'Content-Type': 'application/json',
            //     },
            //     body: JSON.stringify({
            //         productId: productId,
            //         quantity: 1
            //     }),
            // })
            // .then(response => response.json())
            // .then(data => {
            //     if (data.success) {
            //         alert('Produit ajouté au panier !');
            //     } else {
            //         alert('Erreur: ' + data.message);
            //     }
            // });
        });
    }
    
    // Bouton Ajouter aux favoris
    const addToFavBtn = document.querySelector('.btn-outline-secondary');
    if (addToFavBtn) {
        addToFavBtn.addEventListener('click', function() {
            // Simuler l'ajout aux favoris
            alert('Produit ajouté aux favoris !');
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
    card.className = 'col-md-3 mb-4';
    card.innerHTML = `
        <div class="card h-100">
            <img src="${product.image}" class="card-img-top" alt="${product.name}">
            <div class="card-body">
                <h5 class="card-title">${product.name}</h5>
                <p class="card-text font-weight-bold">${product.price} €</p>
                <a href="product.php?id=${product.id}" class="btn btn-sm btn-primary">Voir détails</a>
            </div>
        </div>
    `;
    return card;
}