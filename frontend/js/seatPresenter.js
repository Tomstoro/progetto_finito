/**
 * SeatPresenter — carica tavoli e fasce orarie, invia la prenotazione.
 */
(async function () {
    const roomId = sessionStorage.getItem('roomId');
    const title = document.getElementById('room-title');
    const dateInput = document.getElementById('booking-date');
    const mapContainer = document.getElementById('map-container');
    const timeslotSelect = document.getElementById('timeslot-select');
    const bookBtn = document.getElementById('book-btn');
    const message = document.getElementById('message');

    const holidays = [
        '01-01', '06-01', '25-04', '01-05', '02-06', '15-08', '01-11', '08-12', '25-12', '26-12',
    ];

    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function isHoliday(dateString) {
        const [, month, day] = dateString.match(/^(\d{4})-(\d{2})-(\d{2})$/) || [];
        return month && day ? holidays.includes(`${month}-${day}`) : false;
    }

    function showMessage(text, type = 'info') {
        if (!message) {
            return;
        }
        message.textContent = text;
        message.className = `alert alert-${type} mt-3`;
    }

    function disableBooking() {
        if (timeslotSelect) {
            timeslotSelect.disabled = true;
        }
        if (bookBtn) {
            bookBtn.disabled = true;
        }
    }

    function renderTables(tables) {
        if (!mapContainer) {
            return;
        }

        if (!Array.isArray(tables) || tables.length === 0) {
            mapContainer.innerHTML = '<p class="text-muted">Nessun tavolo libero in questo slot.</p>';
            return;
        }

        mapContainer.innerHTML = `
            <div class="list-group">
                ${tables.map(table => `
                    <label class="list-group-item d-flex justify-content-between align-items-center">
                        <span>
                            <strong>Tavolo ${table.table_number}</strong> · ${table.seat_count} posti
                        </span>
                        <input type="radio" name="table" value="${table.table_id}" />
                    </label>
                `).join('')}
            </div>
        `;
    }

    function getSelectedTimeslotData() {
        const selected = timeslotSelect.selectedOptions[0];
        if (!selected) {
            return null;
        }
        return {
            id: Number(selected.value),
            start: selected.dataset.start,
            end: selected.dataset.end,
            date: dateInput.value,
        };
    }

    function setDateBounds() {
        if (!dateInput) {
            return;
        }

        const today = new Date();
        const maxDate = new Date(today);
        maxDate.setMonth(maxDate.getMonth() + 1);

        dateInput.min = formatDate(today);
        dateInput.max = formatDate(maxDate);

        let initialDate = formatDate(today);
        if (isHoliday(initialDate)) {
            let next = new Date(today);
            do {
                next.setDate(next.getDate() + 1);
                initialDate = formatDate(next);
            } while (isHoliday(initialDate) && next <= maxDate);
        }

        dateInput.value = initialDate;
    }

    async function loadTimeSlots() {
        disableBooking();
        if (!dateInput || !timeslotSelect) {
            return;
        }

        const date = dateInput.value;
        if (!date) {
            timeslotSelect.innerHTML = '<option>Seleziona prima una data</option>';
            renderTables([]);
            return;
        }

        if (isHoliday(date)) {
            showMessage('La data selezionata è un giorno festivo, scegli un altro giorno.', 'warning');
            timeslotSelect.innerHTML = '<option>Giorno festivo</option>';
            renderTables([]);
            return;
        }

        try {
            const slots = await ApiClient.get(`/rooms/${roomId}/timeslots?date=${date}`);
            if (!Array.isArray(slots) || slots.length === 0) {
                timeslotSelect.innerHTML = '<option>Nessuna fascia disponibile</option>';
                renderTables([]);
                showMessage('Non sono disponibili fasce orarie per la data selezionata.', 'warning');
                return;
            }

            timeslotSelect.innerHTML = slots.map(slot => `
                <option value="${slot.id}" data-start="${slot.ora_inizio}" data-end="${slot.ora_fine}">${slot.label}</option>
            `).join('');
            timeslotSelect.disabled = false;
            showMessage('Scegli un orario per visualizzare i tavoli disponibili.', 'info');
            await loadTables();
        } catch (error) {
            showMessage(error.message || 'Errore durante il caricamento delle fasce orarie', 'danger');
            timeslotSelect.innerHTML = '<option>Errore</option>';
            renderTables([]);
        }
    }

    async function loadTables() {
        if (!dateInput || !timeslotSelect) {
            return;
        }

        const slotData = getSelectedTimeslotData();
        if (!slotData || !slotData.start || !slotData.end) {
            renderTables([]);
            disableBooking();
            return;
        }

        try {
            const tables = await ApiClient.get(
                `/rooms/${roomId}/seats?date=${slotData.date}&start=${slotData.start}&end=${slotData.end}`
            );

            renderTables(tables.tables);
            const selectedTable = document.querySelector('input[name="table"]:checked');
            bookBtn.disabled = !selectedTable;
            showMessage('Seleziona un tavolo disponibile e conferma la prenotazione.', 'info');

            document.querySelectorAll('input[name="table"]').forEach(input => {
                input.addEventListener('change', () => {
                    bookBtn.disabled = false;
                });
            });
        } catch (error) {
            showMessage(error.message || 'Errore durante il caricamento dei tavoli', 'danger');
            renderTables([]);
            disableBooking();
        }
    }

    if (!roomId) {
        showMessage('Seleziona un\'aula prima di prenotare.', 'warning');
        setTimeout(() => window.location.replace('rooms.html'), 1200);
        return;
    }

    if (!ApiClient.getToken()) {
        window.location.replace('login.html');
        return;
    }

    if (title) {
        title.textContent = 'Aula #' + roomId;
    }

    if (!dateInput || !timeslotSelect || !mapContainer || !bookBtn) {
        showMessage('Errore di inizializzazione della pagina.', 'danger');
        return;
    }

    setDateBounds();
    await loadTimeSlots();

    dateInput.addEventListener('input', async () => {
        if (isHoliday(dateInput.value)) {
            showMessage('La data selezionata è un giorno festivo, scegli un altro giorno.', 'warning');
            timeslotSelect.innerHTML = '<option>Giorno festivo</option>';
            disableBooking();
            renderTables([]);
            return;
        }

        await loadTimeSlots();
    });

    timeslotSelect.addEventListener('change', async () => {
        await loadTables();
    });

    bookBtn.addEventListener('click', async () => {
        const selectedTable = document.querySelector('input[name="table"]:checked');
        const slotData = getSelectedTimeslotData();

        if (!selectedTable) {
            showMessage('Seleziona un tavolo prima di confermare.', 'warning');
            return;
        }

        if (!slotData || !slotData.id) {
            showMessage('Seleziona una fascia oraria valida.', 'warning');
            return;
        }

        const tableId = Number(selectedTable.value);
        const timeslotId = slotData.id;

        bookBtn.disabled = true;
        bookBtn.textContent = 'Prenotazione in corso...';

        try {
            await ApiClient.post('/bookings', {
                roomId: Number(roomId),
                tableId,
                timeSlotId: timeslotId,
            });

            showMessage('Prenotazione confermata! Reindirizzamento...', 'success');
            setTimeout(() => window.location.replace('bookings.html'), 800);
        } catch (error) {
            showMessage(error.message || 'Errore durante la prenotazione', 'danger');
        } finally {
            bookBtn.disabled = false;
            bookBtn.textContent = 'Conferma prenotazione';
        }
    });
})();
