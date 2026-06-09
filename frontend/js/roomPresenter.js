/**
 * RoomPresenter — mostra l'elenco aule e naviga verso seats.html.
 */
(async function () {
    const listEl = document.getElementById('rooms-list');
    const message = document.getElementById('message');

    if (!listEl || !message) {
        return;
    }

    function showMessage(text, type = 'info') {
        message.textContent = text;
        message.className = `alert alert-${type} mt-3`;
    }

    try {
        const rooms = await ApiClient.get('/rooms');

        if (!Array.isArray(rooms) || rooms.length === 0) {
            listEl.innerHTML = '<p class="text-muted">Nessuna aula attiva trovata.</p>';
            showMessage('Al momento non ci sono aule attive.', 'secondary');
            return;
        }

        listEl.innerHTML = `
            <div class="row row-cols-1 row-cols-md-2 g-4">
                ${rooms.map(room => `
                    <div class="col">
                        <div class="card h-100 room-card" data-room-id="${room.id}" style="cursor: pointer;">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">${room.nome}</h5>
                                <p class="card-text mb-1 text-muted">${room.edificio ?? 'Edificio non specificato'}</p>
                                <p class="card-text mb-1">Piano: ${room.piano !== null ? room.piano : '-'}</p>
                                <p class="card-text mb-3">Capienza: ${room.capienza}</p>
                                <div class="mt-auto">
                                    <span class="badge bg-primary">${room.stato}</span>
                                    <button class="btn btn-sm btn-outline-primary float-end">Apri</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;

        listEl.querySelectorAll('.room-card').forEach(card => {
            card.addEventListener('click', () => {
                const roomId = card.getAttribute('data-room-id');
                sessionStorage.setItem('roomId', roomId);
                window.location.href = 'seats.html';
            });
        });

        message.classList.add('d-none');
    } catch (error) {
        showMessage(error.message || 'Errore durante il caricamento delle aule', 'danger');
    }
})();
