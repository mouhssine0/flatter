/**
 * Categories page enhancement script
 * Adds animations and shadow effects to the categories page
 */

document.addEventListener('DOMContentLoaded', function() {
    // Add animation to category cards
    const categoryCards = document.querySelectorAll('.card');
    
    // Apply entrance animations with staggered delay
    categoryCards.forEach((card, index) => {
        // Add shadow effect on hover
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px)';
            this.style.boxShadow = '0 15px 30px rgba(0, 0, 0, 0.1)';
            this.style.transition = 'all 0.3s ease';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 5px 15px rgba(0, 0, 0, 0.05)';
            this.style.transition = 'all 0.3s ease';
        });
        
        // Initial animation
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100 * index);
    });
    
    // Add pulse animation to buttons
    const buttons = document.querySelectorAll('.btn-primary');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.classList.add('pulse-animation');
        });
        
        button.addEventListener('mouseleave', function() {
            this.classList.remove('pulse-animation');
        });
    });
    
    // Add category title animation
    const pageTitle = document.querySelector('h1');
    if (pageTitle) {
        pageTitle.style.opacity = '0';
        pageTitle.style.transform = 'translateY(-20px)';
        
        setTimeout(() => {
            pageTitle.style.opacity = '1';
            pageTitle.style.transform = 'translateY(0)';
            pageTitle.style.transition = 'all 0.5s ease';
        }, 300);
    }
});