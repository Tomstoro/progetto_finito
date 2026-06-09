(function () {
    const profileName = document.getElementById('profile-name');
    const profileEmail = document.getElementById('profile-email');
    const profileMatricola = document.getElementById('profile-matricola');
    const bookingsList = document.getElementById('bookings-list');
    const bookingsEmpty = document.getElementById('bookings-empty');
    const passwordForm = document.getElementById('password-form');
    const message = document.getElementById('message');
    const logoutButtons = document.querySelectorAll('#logout-btn, #logout-btn-top');

    if (!profileName || !profileEmail || !profileMatricola || !bookingsList || !passwordForm || !message) {
        return;
    }

    function showMessage(text, type = 'info') {
        message.textContent = text;
        message.className = `alert alert-${type} mt-3`;
        message.classList.remove('d-none');
    }

    function clearMessage() {
        message.className = 'alert d-none';
        message.textContent = '';
    }

    function renderBookings(bookings) {
        bookingsList.innerHTML = '';
        bookingsEmpty.style.display = bookings.length ? 'none' : 'block';

        bookings.forEach((booking) => {
            const statusClass = booking.stato === 'confermata' ? 'status-confirmed' : 'status-cancelled';
            const statusLabel = booking.stato === 'confermata' ? 'Confermata' : 'Cancellata';

            const card = document.createElement('div');
            card.className = 'booking-card';
            card.innerHTML = `
                <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                    <div>
                        <div class="h6 mb-1">${booking.room_name} — Tavolo ${booking.table_number}</div>
                        <div class="booking-meta">${booking.data_prenotazione} • ${booking.ora_inizio} - ${booking.ora_fine}</div>
                    </div>
                    <span class="booking-status ${statusClass}">${statusLabel}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center gap-2 mt-3">
                    <div class="text-muted small">ID: ${booking.id}</div>
                    ${booking.stato === 'confermata' ? '<button class="btn btn-outline-danger btn-sm cancel-btn" data-booking-id="' + booking.id + '">Annulla</button>' : ''}
                </div>
            `;

            bookingsList.appendChild(card);
        });

        document.querySelectorAll('.cancel-btn').forEach((button) => {
            button.addEventListener('click', async () => {
                const bookingId = button.getAttribute('data-booking-id');
                if (!bookingId || !confirm('Sei sicuro di voler cancellare questa prenotazione?')) {
                    return;
                }
                try {
                    await ApiClient.deleteRequest(`/bookings/${bookingId}`);
                    showMessage('Prenotazione annullata con successo.', 'success');
                    loadBookings();
                } catch (error) {
                    showMessage(error.message || 'Errore durante la cancellazione.', 'danger');
                }
            });
        });
    }

    async function loadProfile() {
        try {
            const profile = await ApiClient.get('/auth/profile');
            profileName.textContent = `${profile.nome} ${profile.cognome}`;
            profileEmail.textContent = profile.email;
            profileMatricola.textContent = profile.matricola || 'N/D';
        } catch (error) {
            ApiClient.logout();
        }
    }

    async function loadBookings() {
        try {
            const bookings = await ApiClient.get('/bookings?me=1');
            renderBookings(bookings);
        } catch (error) {
            showMessage(error.message || 'Errore durante il caricamento delle prenotazioni.', 'danger');
        }
    }

    passwordForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearMessage();

        const currentPassword = document.getElementById('currentPassword').value.trim();
        const newPassword = document.getElementById('newPassword').value.trim();

        if (!currentPassword || !newPassword) {
            showMessage('Compila i campi della password.', 'warning');
            return;
        }

        try {
            await ApiClient.post('/auth/change-password', { currentPassword, newPassword });
            showMessage('Password aggiornata con successo.', 'success');
            passwordForm.reset();
        } catch (error) {
            showMessage(error.message || 'Errore durante l&apos;aggiornamento della password.', 'danger');
        }
    });

    logoutButtons.forEach((button) => {
        button.addEventListener('click', () => ApiClient.logout());
    });

    loadProfile();
    loadBookings();
})();
