@extends('tyro-dashboard::layouts.app')

@section('title', 'Calendar')

@section('breadcrumb')
<a href="{{ route('tyro-dashboard.index') }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Calendar</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Meeting Calendar</h1>
            <p class="page-description" style="font-size: 1rem;">View all your meetings in calendar format.</p>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="{{ route('dashboard.my-meetings') }}" class="btn btn-secondary">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: inline-block; margin-right: 8px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                </svg>
                List View
            </a>
            <a href="{{ route('dashboard.create-meeting') }}" class="btn btn-primary">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: inline-block; margin-right: 8px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create Meeting
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div id="calendar"></div>
    </div>
</div>

<link href='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/main.min.css' rel='stylesheet' />
<link href='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.10/main.min.css' rel='stylesheet' />
<link href='https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@6.1.10/main.min.css' rel='stylesheet' />

<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.10/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@6.1.10/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/list@6.1.10/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/interaction@6.1.10/index.global.min.js'></script>

<style>
    #calendar {
        max-width: 100%;
        margin: 0 auto;
    }

    .fc {
        font-family: inherit;
    }

    .fc-toolbar-title {
        font-size: 1.5rem !important;
        font-weight: 600;
    }

    .fc-button {
        background-color: #667eea !important;
        border-color: #667eea !important;
        text-transform: capitalize;
    }

    .fc-button:hover {
        background-color: #5568d3 !important;
        border-color: #5568d3 !important;
    }

    .fc-button-active {
        background-color: #4c51bf !important;
        border-color: #4c51bf !important;
    }

    .fc-event {
        cursor: pointer;
        border: none !important;
    }

    .fc-daygrid-event {
        padding: 2px 4px;
    }

    .fc-event-title {
        font-weight: 500;
    }

    .fc-list-event:hover td {
        background-color: #f3f4f6;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
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
            url: '{{ route('dashboard.calendar.events') }}',
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
</script>
@endsection
