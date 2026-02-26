(() => {
  const cfg = window.MEETING_BOOTSTRAP || {};
  const joinBtn = document.getElementById('joinBtn');
  const statusText = document.getElementById('statusText');
  const entry = document.getElementById('entry');
  const container = document.getElementById('jitsi-container');
  let jitsiApi = null;

  const errorMap = {
    ERR_IP_NOT_ALLOWED: 'Your network is not allowed for this meeting.',
    ERR_MEETING_FULL: 'Meeting is full right now.',
    ERR_INVALID_PASSWORD: 'Meeting password is invalid.',
    ERR_GUEST_NOT_ALLOWED: 'Guests are not allowed for this meeting.',
    ERR_OUTSIDE_JOIN_WINDOW: 'Meeting is not joinable at this time.',
    ERR_JWT_REQUIRED_NOT_CONFIGURED: 'Meeting security configuration is incomplete. Contact admin.',
    ERR_HEALTH_UNREACHABLE: 'Meeting service is unreachable. Please retry in a moment.',
    ERR_HEALTH_JITSI_API_MISSING: 'Meeting interface not loaded. Please refresh.'
  };

  const setStatus = (msg) => { if (statusText) statusText.textContent = msg; };

  async function healthCheck() {
    const response = await fetch(`${cfg.apiBase}/meetings/${cfg.meetingId}/health`, {
      method: 'GET',
      headers: { 'Accept': 'application/json' },
      credentials: 'same-origin'
    });

    const data = await response.json();
    if (!response.ok || !data.ok) {
      const code = data.error_code || 'ERR_HEALTH_UNREACHABLE';
      throw new Error(errorMap[code] || data.message || 'Health check failed');
    }

    if (typeof JitsiMeetExternalAPI === 'undefined') {
      throw new Error(errorMap.ERR_HEALTH_JITSI_API_MISSING);
    }
  }

  async function joinMeeting() {
    try {
      if (joinBtn) joinBtn.disabled = true;
      setStatus('Checking meeting service…');
      await healthCheck();

      setStatus('Authorizing join…');
      const res = await fetch(`${cfg.apiBase}/meetings/${cfg.meetingId}/join`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Accept': 'application/json'
        },
        credentials: 'same-origin',
        body: JSON.stringify({})
      });

      const data = await res.json();
      if (!res.ok || !data.can_join) {
        const code = data.error_code || 'ERR_JOIN_DENIED';
        throw new Error(errorMap[code] || data.message || 'Unable to join meeting');
      }

      entry.style.display = 'none';
      container.style.display = 'block';

      const options = {
        roomName: data.room_name,
        width: '100%',
        height: '100%',
        parentNode: container,
        userInfo: {
          displayName: data.display_name || 'Guest',
          email: data.config?.userInfo?.email || '',
          avatarURL: data.avatar_url || data.config?.userInfo?.avatarURL || ''
        },
        configOverwrite: {
          prejoinPageEnabled: false,
          prejoinConfig: { enabled: false, hideDisplayName: true },
          requireDisplayName: false,
          enableWelcomePage: false,
          enableClosePage: false
        }
      };

      if (data.jwt) options.jwt = data.jwt;
      jitsiApi = new JitsiMeetExternalAPI(data.domain, options);

      jitsiApi.addEventListener('readyToClose', async () => {
        try {
          await fetch(`${cfg.apiBase}/meetings/${cfg.meetingId}/leave`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'same-origin',
            keepalive: true
          });
        } catch (_) {}
        window.location.href = cfg.dashboardUrl;
      });

    } catch (err) {
      setStatus(err.message || 'Join failed');
      if (joinBtn) {
        joinBtn.disabled = false;
        joinBtn.style.display = 'inline-block';
      }
      alert(err.message || 'Join failed');
    }
  }

  if (cfg.status === 'ended') return;

  if (joinBtn) {
    joinBtn.addEventListener('click', joinMeeting);
  }

  // Zoom-like one-click behavior:
  // user clicks join on meeting page, then this live shell auto-connects.
  if (joinBtn) joinBtn.style.display = 'none';
  setStatus('Auto-joining…');
  joinMeeting();
})();
