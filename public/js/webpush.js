(function (window, navigator) {
  "use strict";

  var cfg = window.MVGX_WEBPUSH || {};
  var storageKey = "mvgx_push_prompt_dismissed";

  function urlBase64ToUint8Array(base64String) {
    var padding = "=".repeat((4 - (base64String.length % 4)) % 4);
    var base64 = (base64String + padding).replace(/-/g, "+").replace(/_/g, "/");
    var rawData = window.atob(base64);
    var outputArray = new Uint8Array(rawData.length);
    for (var i = 0; i < rawData.length; ++i) {
      outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
  }

  function isSupported() {
    return (
      "serviceWorker" in navigator &&
      "PushManager" in window &&
      "Notification" in window &&
      !!cfg.vapidPublicKey
    );
  }

  function getCsrf() {
    return (
      cfg.csrfToken ||
      (document.querySelector('meta[name="csrf-token"]') || {}).content ||
      ""
    );
  }

  function postJson(url, body) {
    return fetch(url, {
      method: "POST",
      credentials: "same-origin",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        "X-CSRF-TOKEN": getCsrf(),
        "X-Requested-With": "XMLHttpRequest",
      },
      body: JSON.stringify(body || {}),
    }).then(function (res) {
      if (!res.ok) {
        return res.json().catch(function () {
          return {};
        }).then(function (data) {
          var err = new Error((data && data.message) || "Request failed");
          err.status = res.status;
          throw err;
        });
      }
      return res.json().catch(function () {
        return { ok: true };
      });
    });
  }

  function getRegistration() {
    return navigator.serviceWorker.ready;
  }

  function currentSubscription() {
    return getRegistration().then(function (reg) {
      return reg.pushManager.getSubscription();
    });
  }

  function permissionState() {
    if (!isSupported()) return "unsupported";
    return Notification.permission;
  }

  function syncSubscription(subscription) {
    if (!subscription || !cfg.subscribeUrl) {
      return Promise.resolve({ ok: false });
    }
    var json = subscription.toJSON();
    return postJson(cfg.subscribeUrl, {
      endpoint: json.endpoint,
      keys: json.keys || {},
      contentEncoding:
        (window.PushManager &&
          PushManager.supportedContentEncodings &&
          PushManager.supportedContentEncodings[0]) ||
        "aesgcm",
    });
  }

  function enable() {
    if (!isSupported()) {
      return Promise.reject(new Error("Thiết bị hoặc trình duyệt không hỗ trợ thông báo đẩy."));
    }

    return getRegistration()
      .then(function (reg) {
        return Notification.requestPermission().then(function (permission) {
          if (permission !== "granted") {
            throw new Error("Bạn chưa cho phép thông báo. Hãy bật lại trong cài đặt trình duyệt / PWA.");
          }
          return reg.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(cfg.vapidPublicKey),
          });
        });
      })
      .then(function (subscription) {
        return syncSubscription(subscription).then(function () {
          window.localStorage.removeItem(storageKey);
          window.dispatchEvent(new CustomEvent("mvgx:push-changed", { detail: { enabled: true } }));
          return subscription;
        });
      });
  }

  function disable() {
    return currentSubscription().then(function (subscription) {
      if (!subscription) {
        window.dispatchEvent(new CustomEvent("mvgx:push-changed", { detail: { enabled: false } }));
        return { ok: true };
      }
      var endpoint = subscription.endpoint;
      return subscription.unsubscribe().then(function () {
        if (cfg.unsubscribeUrl) {
          return postJson(cfg.unsubscribeUrl, { endpoint: endpoint });
        }
        return { ok: true };
      }).then(function (result) {
        window.dispatchEvent(new CustomEvent("mvgx:push-changed", { detail: { enabled: false } }));
        return result;
      });
    });
  }

  function ensureSynced() {
    if (!isSupported() || Notification.permission !== "granted") {
      return Promise.resolve(null);
    }
    return currentSubscription().then(function (subscription) {
      if (!subscription) {
        return enable().catch(function () {
          return null;
        });
      }
      return syncSubscription(subscription).then(function () {
        return subscription;
      });
    });
  }

  function getStatus() {
    if (!isSupported()) {
      return Promise.resolve({ supported: false, permission: "unsupported", subscribed: false });
    }
    return currentSubscription().then(function (subscription) {
      return {
        supported: true,
        permission: Notification.permission,
        subscribed: !!subscription,
      };
    });
  }

  window.MvgxWebPush = {
    isSupported: isSupported,
    permissionState: permissionState,
    enable: enable,
    disable: disable,
    ensureSynced: ensureSynced,
    getStatus: getStatus,
  };

  window.notificationBellPush = function () {
    return {
      open: false,
      pushSupported: false,
      pushEnabled: false,
      pushBusy: false,
      pushError: "",
      pushHint: "Nhận thông báo ngay cả khi đã thoát ứng dụng.",
      init: function () {
        var self = this;
        this.refreshPushStatus();
        if (navigator.serviceWorker) {
          navigator.serviceWorker.addEventListener("message", function (event) {
            if (event.data && event.data.type === "mvgx:notification") {
              if (window.Livewire) {
                window.Livewire.emit("notificationUpdated");
              }
            }
            if (event.data && event.data.type === "mvgx:navigate" && event.data.url) {
              window.location.href = event.data.url;
            }
          });
        }
      },
      refreshPushStatus: function () {
        var self = this;
        var api = window.MvgxWebPush;
        if (!api || !api.isSupported()) {
          this.pushSupported = false;
          return;
        }
        this.pushSupported = true;
        api.getStatus()
          .then(function (status) {
            self.pushEnabled = !!(status.subscribed && status.permission === "granted");
            if (status.permission === "denied") {
              self.pushHint =
                "Quyền thông báo đang bị chặn. Mở lại trong cài đặt trình duyệt / PWA.";
            } else if (self.pushEnabled) {
              self.pushHint = "Đã bật — bạn sẽ nhận thông báo khi thoát app.";
            } else {
              self.pushHint = "Nhận thông báo ngay cả khi đã thoát ứng dụng.";
            }
          })
          .catch(function () {
            self.pushSupported = false;
          });
      },
      togglePush: function () {
        var self = this;
        var api = window.MvgxWebPush;
        if (!api || this.pushBusy) return;
        this.pushBusy = true;
        this.pushError = "";
        var turningOn = !this.pushEnabled;
        var action = turningOn ? api.enable() : api.disable();
        action
          .then(function () {
            window.dispatchEvent(
              new CustomEvent("toast", {
                detail: [
                  "success",
                  turningOn ? "Đã bật thông báo đẩy." : "Đã tắt thông báo đẩy.",
                ],
              })
            );
          })
          .catch(function (err) {
            self.pushError =
              (err && err.message) || "Không thể cập nhật thông báo đẩy.";
          })
          .finally(function () {
            self.pushBusy = false;
            self.refreshPushStatus();
          });
      },
    };
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", function () {
      ensureSynced().catch(function () {});
    });
  } else {
    ensureSynced().catch(function () {});
  }
})(window, navigator);
