/**
 * Cookie consent: localStorage + синхронизация с сервером (дедуп на бэкенде).
 * Яндекс.Метрика и произвольный код из настроек подгружаются только при analytics === true.
 */

const STORAGE_KEY = 'snovidec_cookie_consent_v1';
const CLIENT_ID_KEY = 'snovidec_consent_client_id';

function getComplianceConfig() {
    return window.__COMPLIANCE__ || {};
}

function parseConsent(raw) {
    try {
        const o = JSON.parse(raw);
        if (
            o
            && typeof o.policyVersion === 'string'
            && typeof o.necessary === 'boolean'
            && typeof o.analytics === 'boolean'
        ) {
            return o;
        }
    } catch (_) {}
    return null;
}

function readConsent() {
    try {
        return parseConsent(localStorage.getItem(STORAGE_KEY));
    } catch (_) {
        return null;
    }
}

function writeConsent(partial) {
    const cfg = getComplianceConfig();
    const prev = readConsent();
    const next = {
        policyVersion: cfg.policyVersion || '1.1',
        necessary: true,
        analytics: false,
        updatedAt: Date.now(),
        ...prev,
        ...partial,
        necessary: true,
    };
    localStorage.setItem(STORAGE_KEY, JSON.stringify(next));
    return next;
}

function getOrCreateClientId() {
    try {
        let id = localStorage.getItem(CLIENT_ID_KEY);
        if (id && /^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i.test(id)) {
            return id;
        }
        id = crypto.randomUUID();
        localStorage.setItem(CLIENT_ID_KEY, id);
        return id;
    } catch (_) {
        return null;
    }
}

function fingerprintConsent(c) {
    return `${c.policyVersion}|${c.necessary}|${c.analytics}`;
}

let lastLoggedFingerprint = null;

function logConsentToServer(consent) {
    const cfg = getComplianceConfig();
    const url = cfg.consentLogUrl;
    const clientId = getOrCreateClientId();
    if (!url || !clientId || !cfg.policyVersion) {
        return;
    }

    const fp = fingerprintConsent(consent);
    if (fp === lastLoggedFingerprint) {
        return;
    }

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': token || '',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
            client_id: clientId,
            policy_version: cfg.policyVersion,
            necessary: consent.necessary,
            analytics: consent.analytics,
        }),
    })
        .then(() => {
            lastLoggedFingerprint = fp;
        })
        .catch(() => {});
}

function clearLikelyYandexCookies() {
    const hostParts = window.location.hostname.split('.');
    const paths = ['/', window.location.pathname].filter(Boolean);
    const names = ['_ym_uid', '_ym_d', '_ym_isad', '_ym_visorc', '_ym_wasSynced'];

    for (let i = hostParts.length - 1; i >= 0; i--) {
        const domain = hostParts.slice(i).join('.');
        const domainAttr = domain ? `;domain=.${domain}` : '';
        for (const name of names) {
            for (const path of paths) {
                document.cookie = `${name}=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=${path}${domainAttr}`;
            }
        }
    }
}

let analyticsLoaded = false;

function injectDeferredHeadAdFromTemplate() {
    const cfg = getComplianceConfig();
    if (!cfg.hasDeferredHeadAd) {
        return;
    }
    const tpl = document.getElementById('deferred-global-head-ad');
    if (!tpl || !tpl.content || tpl.dataset.applied === '1') {
        return;
    }
    tpl.dataset.applied = '1';
    document.head.appendChild(tpl.content.cloneNode(true));
}

function loadYandexMetrika(metrikaId) {
    if (!metrikaId || analyticsLoaded) {
        return;
    }
    analyticsLoaded = true;
    (function (m, e, t, r, i, k, a) {
        m[i] =
            m[i] ||
            function () {
                (m[i].a = m[i].a || []).push(arguments);
            };
        m[i].l = 1 * new Date();
        for (let j = 0; j < document.scripts.length; j++) {
            if (document.scripts[j].src === r) {
                return;
            }
        }
        k = e.createElement(t);
        a = e.getElementsByTagName(t)[0];
        k.async = 1;
        k.src = r;
        a.parentNode.insertBefore(k, a);
    })(window, document, 'script', `https://mc.yandex.ru/metrika/tag.js?id=${metrikaId}`, 'ym');
    window.ym(metrikaId, 'init', {
        ssr: true,
        webvisor: true,
        clickmap: true,
        accurateTrackBounce: true,
        trackLinks: true,
    });
    const ns = document.createElement('noscript');
    ns.innerHTML = `<div><img src="https://mc.yandex.ru/watch/${metrikaId}" style="position:absolute; left:-9999px;" alt="" /></div>`;
    document.body.appendChild(ns);
}

function applyAnalyticsFromConsent(consent) {
    const cfg = getComplianceConfig();
    if (!cfg.deferContext) {
        return;
    }

    if (!consent.analytics) {
        analyticsLoaded = false;
        clearLikelyYandexCookies();
        return;
    }

    const meta = document.querySelector('meta[name="deferred-metrika-id"]');
    const metrikaId = meta?.getAttribute('content')?.replace(/\D/g, '') || '';
    if (metrikaId) {
        loadYandexMetrika(metrikaId);
    }
    injectDeferredHeadAdFromTemplate();
}

function showBanner(el) {
    el.classList.remove('hidden');
}

function hideBanner(el) {
    el.classList.add('hidden');
}

function openModal(modal, analyticsCheckbox, consent) {
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    analyticsCheckbox.checked = !!consent.analytics;
}

function closeModal(modal) {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

export function initCookieConsent() {
    const cfg = getComplianceConfig();
    if (!cfg.deferContext) {
        return;
    }

    const banner = document.getElementById('cookie-consent-banner');
    const modal = document.getElementById('cookie-consent-modal');
    const analyticsCheckbox = document.getElementById('cookie-opt-analytics');

    let consent = readConsent();

    if (consent && consent.policyVersion === cfg.policyVersion) {
        lastLoggedFingerprint = fingerprintConsent(consent);
        applyAnalyticsFromConsent(consent);
        if (banner) {
            hideBanner(banner);
        }
    } else if (consent && consent.policyVersion !== cfg.policyVersion) {
        consent = writeConsent({
            policyVersion: cfg.policyVersion,
            necessary: true,
            analytics: false,
            updatedAt: Date.now(),
        });
        logConsentToServer(consent);
        applyAnalyticsFromConsent(consent);
        if (banner) {
            showBanner(banner);
        }
    } else if (banner) {
        showBanner(banner);
    }

    function persistChoice(analyticsEnabled) {
        consent = writeConsent({
            analytics: analyticsEnabled,
            necessary: true,
            policyVersion: cfg.policyVersion,
        });
        logConsentToServer(consent);
        applyAnalyticsFromConsent(consent);
        if (banner) {
            hideBanner(banner);
        }
    }

    if (banner) {
        document.getElementById('cookie-btn-necessary')?.addEventListener('click', () => persistChoice(false));
        document.getElementById('cookie-btn-accept-all')?.addEventListener('click', () => persistChoice(true));
        document.getElementById('cookie-btn-settings')?.addEventListener('click', () => {
            openModal(modal, analyticsCheckbox, consent || { analytics: false });
        });
    }

    if (modal && analyticsCheckbox) {
        document.getElementById('cookie-modal-save')?.addEventListener('click', () => {
            persistChoice(analyticsCheckbox.checked);
            closeModal(modal);
        });
        document.getElementById('cookie-modal-cancel')?.addEventListener('click', () => closeModal(modal));
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal(modal);
            }
        });
    }

    window.addEventListener('open-cookie-settings', () => {
        const c = readConsent() || { analytics: false };
        if (modal && analyticsCheckbox) {
            openModal(modal, analyticsCheckbox, c);
        }
    });
}
