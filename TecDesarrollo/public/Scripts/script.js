<<<<<<< HEAD
document.querySelectorAll('.asignarTecnicoModal').forEach(button => {
    button.addEventListener('click', function() {
        let id = this.getAttribute('data-id');  
        document.getElementById('id_ticket').value = id;
        console.log("Ticket ID asignado al input oculto:", id);
    });
});

function redirigirTicket(form, id) {
    form.action = `../Tickets/ver_ticket.php?id=${id}`;
    form.submit();
=======
document.querySelectorAll('.asignarTecnicoModal').forEach(button => {
    button.addEventListener('click', function() {
        let id = this.getAttribute('data-id');  
        document.getElementById('id_ticket').value = id;
        console.log("Ticket ID asignado al input oculto:", id);
    });
});

function redirigirTicket(form, id) {
    form.action = `../Tickets/ver_ticket.php?id=${id}`;
    form.submit();
>>>>>>> 19b4cc9b5eb857dcd6df0e85b8a44f66b1b55ff0
}