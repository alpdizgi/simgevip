document.addEventListener('DOMContentLoaded', () => {
    const loginBox = document.getElementById('loginBox');
    
    // Add a slight delay for a nice entrance animation
    setTimeout(() => {
        if(loginBox) {
            loginBox.classList.add('show');
        }
    }, 100);

    // Add particle effects or subtle background animations if desired later
    console.log("Admin Login Loaded.");
});
