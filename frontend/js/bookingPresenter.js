/**
 * BookingPresenter — mostra le prenotazioni dell'utente autenticato.
 */
(async function () {
    const tbody = document.querySelector('#bookings-table tbody');
    const message = document.getElementById('message');

    if (!tbody || !message) {
        return;
    }

    function showMessage(text, type = 'info') {
        message.textContent = text;
        message.className = `alert alert-${type} mt-3`;
    }

    if (!ApiClient.getToken()) {
        window.location.replace('login.html');
        return;
    }

    try {
        const bookings = await ApiClient.get('/bookings?me=true');

        if (!Array.isArray(bookings) || bookings.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Nessuna prenotazione trovata.</td></tr>';
            showMessage('Non hai ancora prenotazioni.', 'info');
            return;
        }

        tbody.innerHTML = bookings.map(booking => `
            <tr>
                <td>${booking.room_name}</td>
                <td>${booking.table_number}</td>
                <td>${booking.data_prenotazione} ${booking.ora_inizio} - ${booking.ora_fine}</td>
                <td>${booking.stato}</td>
                <td>${booking.data_prenotazione}</td>
            </tr>
        `).join('');

        message.classList.add('d-none');
    } catch (error) {
        showMessage(error.message || 'Errore durante il caricamento delle prenotazioni', 'danger');
    }
})();
