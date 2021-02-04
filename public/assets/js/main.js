let windowObjectReference = null;
let previousUrl = null;
let tokens

const initializeCalendarPage = () => {
    loadCalendarList()
    initializeCalendar()
}

const initializeCalendar = () => {

    let calendarEl = document.getElementById('calendar');
    calendar = new FullCalendar.Calendar(calendarEl, {
        themeSystem: 'bootstrap',
        initialView: 'dayGridMonth',
        height: 'auto',
        contentHeight: '100%',
        locale: 'ru',
        events: {
            url: `/api/events`,
        },
        headerToolbar: {
            start: 'title',
            center: 'dayGridMonth timeGridWeek',
            end: 'today prev,next'
        },
        eventDidMount: function (info) {
            if (!info.event.extendedProps || !info.event.extendedProps.description) {
                console.log(info)
                return
            }
            $(info.el).tooltip({
                title: info.event.extendedProps.description,
                placement: "top",
                trigger: "hover",
                container: "body"
            });

        },
    });

    calendar.render();
}

const loadCalendarList = () => {
    $("#calendars").empty()
    $.get(`/api/calendars`, (calendars) => {
            for (let calendar of calendars) {
                $("#calendars").append(getCalendarItem(calendar))
            }
        }
    )
}

const getCalendarItem = (calendar) => {
    return `
        <div class="form-check calendar-item">
            <input class="form-check-input" type="checkbox" value="${calendar.id}" onclick="updateCalendar(${calendar.id})"
                   id="calendarCheckbox${calendar.id}" ${calendar.isShow ? "checked" : ""}>
            <label class="form-check-label" for="calendarCheckbox${calendar.id}">
                ${calendar.name}
            </label>
            <small class="sync-date">${formatDate(new Date(calendar.lastSyncDate))}</small>
        </div>
    `
}

const formatDate = (date) => {
    const options = {year: 'numeric', month: 'numeric', day: 'numeric', hour: 'numeric', minute: 'numeric'};
    return date.toLocaleDateString(window.navigator.language.slice(0, 2) !== "en", options)
}

const syncAllEvents = () => {
    $.get("/api/calendars/sync", () => {
        loadCalendarList()
        calendar.getEventSources()[0].refetch()
    })
}

const getWindowFeatures = () => {
    const width = 400;
    const height = 600;
    const top = Math.round((window.innerHeight - height) / 2);
    const left = Math.round((window.innerWidth - width) / 2);
    return `toolbar=no, menubar=no, width=600, height=700, top=${top}, left=${left}`
}

const getOauthPopup = () => {
    $.get("/api/google/url", url => {
        window.removeEventListener('message', oauthResultReceive);
        const strWindowFeatures = getWindowFeatures();

        if (windowObjectReference === null || windowObjectReference.closed) {
            windowObjectReference = window.open(url, name, strWindowFeatures);
        } else if (previousUrl !== url) {
            windowObjectReference = window.open(url, name, strWindowFeatures);
            windowObjectReference.focus();
        } else {
            windowObjectReference.focus();
        }
        window.addEventListener('message', oauthResultReceive, false);
        previousUrl = url;
    })
}

const oauthResultReceive = event => {
    if (event.origin !== window.location.origin) {
        return;
    }
    const {data} = event;
    tokens = data;
    getGoogleCalendars()
}

const getGoogleCalendars = () => {
    if (!tokens)
        return

    $.get("/api/google/calendars?accessToken=" + tokens.accessToken, result => {
        console.log(result)
        let select = $("#selectCalendar")
        select.empty()
        for (let calendar of result) {
            select.append(`<option value="${calendar.id}">${calendar.name}</option>`)
        }
        $("#addCalendarModal").modal()
    })
}

const addNewGoogleCalendar = () => {
    $("#addCalendarModal").modal('hide')
    let selectedCalendar = $("#selectCalendar option:selected")[0]
    $.post("/api/google/new", {
        "accessToken": tokens.accessToken,
        "refreshToken": tokens.refreshToken,
        "calendarId": $("#selectCalendar").val(),
        "calendarName": selectedCalendar.text
    }, () => {
        loadCalendarList()
        calendar.getEventSources()[0].refetch()
    })

}

const updateCalendar = (calendarId) => {
    $.get(`/api/calendars/${calendarId}/changeStatus`, (result) => {
        $(`#calendarCheckbox${result.id}`).checked = result.isShow;
        calendar.getEventSources()[0].refetch()
    })
}