import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';

// Make Calendar globally available for the calendar page
window.FullCalendar = { Calendar };

// Initialize calendar when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');

    if (!calendarEl) {
        return; // Calendar element not found, exit
    }

    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        buttonText: {
            today: 'Today',
            month: 'Month',
            week: 'Week',
            day: 'Day',
            list: 'List'
        },
        height: 'auto',
        navLinks: true,
        editable: false,
        dayMaxEvents: true,
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            meridiem: 'short'
        },
        events: {
            url: window.calendarEventsUrl || '/dashboard/calendar/events',
            method: 'GET',
            failure: function() {
                alert('There was an error loading calendar events!');
            }
        },
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            if (info.event.url) {
                window.open(info.event.url, '_blank');
            }
        },
        eventDidMount: function(info) {
            // Add tooltip with description
            if (info.event.extendedProps.description) {
                info.el.title = info.event.extendedProps.description;
            }
        }
    });

    calendar.render();
});
