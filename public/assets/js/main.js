let windowObjectReference = null;
let previousUrl = null;

function initializeCalendarPage() {
    initializeCalendar()
    loadAllEvents()
}

function initializeCalendar() {

    let calendarEl = document.getElementById('calendar');
    calendar = new FullCalendar.Calendar(calendarEl, {
        themeSystem: 'bootstrap',
        initialView: 'dayGridMonth',
        height: 'auto',
        contentHeight: 'auto'
    });

    calendar.render();
}

function loadAllEvents() {
    for (let input of document.forms.calendars.getElementsByTagName("input")) {
        if (!input.checked) {
            continue;
        }
        loadEvent(input.value)
    }
}

function loadEvent(calendarId) {
    $.get(`/api/calendars/${calendarId}/events`, function (data) {
        for (let item of data) {
            addEventToCalendar(item.name, item.startTime, item.endTime, item.isAllDay)
        }
    })
}

function addEventToCalendar(name, start, end = null, isAllDay = true) {
    calendar.addEvent({
        title: name,
        start: start,
        allDay: isAllDay,
        end: end
    });
}

const getWindowFeatures = () => {
    const width = 400;
    const height = 600;
    const top = Math.round((window.innerHeight - height) / 2);
    const left = Math.round((window.innerWidth - width) / 2);
    return `toolbar=no, menubar=no, width=600, height=700, top=${top}, left=${left}`
}

function addNewCalendar() {


    $.get("/api/google/url", url => {
        window.removeEventListener('message', receiveMessage);
        const strWindowFeatures = getWindowFeatures();

        if (windowObjectReference === null || windowObjectReference.closed) {
            windowObjectReference = window.open(url, name, strWindowFeatures);
        } else if (previousUrl !== url) {
            windowObjectReference = window.open(url, name, strWindowFeatures);
            windowObjectReference.focus();
        } else {
            windowObjectReference.focus();
        }
        window.addEventListener('message', event => receiveMessage(event), false);
        previousUrl = url;
    })

    // $('#addCalendarModal').modal()
}

const receiveMessage = event => {
    // Do we trust the sender of this message? (might be
    // different from what we originally opened, for example).
    if (event.origin !== BASE_URL) {
        return;
    }
    const {data} = event;
    // if we trust the sender and the source is our popup
    if (data.source === 'lma-login-redirect') {
        // get the URL params and redirect to our server to use Passport to auth/login
        const {payload} = data;
        const redirectUrl = `/auth/google/login${payload}`;
        window.location.pathname = redirectUrl;
    }
};