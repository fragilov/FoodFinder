/* ============================================================
   GEO.JS — Geolocation, fallback, trạng thái vị trí
   ============================================================ */

import { CONFIG } from './config.js';

/**
 * Lấy vị trí hiện tại qua Geolocation API
 * @returns {Promise<{lat: number, lng: number, source: string}>}
 */
export function getCurrentPosition() {
  return new Promise((resolve) => {
    if (!navigator.geolocation) {
      resolve({
        lat: CONFIG.FALLBACK_LAT,
        lng: CONFIG.FALLBACK_LNG,
        source: 'fallback',
        error: 'Trình duyệt không hỗ trợ định vị',
      });
      return;
    }

    navigator.geolocation.getCurrentPosition(
      (pos) => {
        resolve({
          lat: pos.coords.latitude,
          lng: pos.coords.longitude,
          source: 'gps',
          error: null,
        });
      },
      (err) => {
        let errorMsg;
        switch (err.code) {
          case err.PERMISSION_DENIED:
            errorMsg = 'Chưa bật được định vị. Chạm vào bản đồ để đặt vị trí, hoặc nhập địa chỉ.';
            break;
          case err.POSITION_UNAVAILABLE:
            errorMsg = 'Không xác định được vị trí. Dùng vị trí mặc định.';
            break;
          case err.TIMEOUT:
            errorMsg = 'Hết thời gian định vị. Dùng vị trí mặc định.';
            break;
          default:
            errorMsg = 'Không lấy được vị trí. Dùng vị trí mặc định.';
        }
        resolve({
          lat: CONFIG.FALLBACK_LAT,
          lng: CONFIG.FALLBACK_LNG,
          source: 'fallback',
          error: errorMsg,
        });
      },
      {
        enableHighAccuracy: true,
        timeout: CONFIG.GEO_TIMEOUT,
        maximumAge: 60000, // cache 1 phút
      }
    );
  });
}
