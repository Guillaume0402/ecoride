document.addEventListener('DOMContentLoaded', () => {   
    const alerts = document.querySelectorAll('.auto-dismiss');  

    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = "opacity 0.5s ease";
            alert.style.opacity = 0;
            setTimeout(() => alert.remove(), 500);
        }, 4000);
    });
});
