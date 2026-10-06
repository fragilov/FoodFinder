/* ============================================================
   CONFIG.JS — Hằng số ứng dụng
   ============================================================ */

export const CONFIG = {
  // API base (đổi khi deploy)
  API_BASE: 'api',

  // Toạ độ fallback: TDTU, Quận 7, TP.HCM
  FALLBACK_LAT: 10.7326,
  FALLBACK_LNG: 106.6996,

  // Bán kính mặc định (km)
  DEFAULT_RADIUS: 1,

  // Danh sách bán kính cho phép
  ALLOWED_RADII: [0.5, 1, 2, 3, 5],

  // Geolocation timeout (ms)
  GEO_TIMEOUT: 8000,

  // Debounce delay (ms)
  DEBOUNCE_MS: 300,

  // Phân trang
  DEFAULT_LIMIT: 50,
  MAX_LIMIT: 100,

  // Map
  DEFAULT_ZOOM: 13,
  FOCUS_ZOOM: 16,
  FLY_DURATION: 1.1,

  // Tile URLs — dùng OpenStreetMap standard (hoàn toàn miễn phí, không watermark)
  TILE_LIGHT: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
  TILE_DARK: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
  TILE_ATTR: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
  TILE_SUBDOMAINS: '',

  // Marker animation
  MARKER_STAGGER_MS: 28,
  MAX_ANIMATED_MARKERS: 12,

  // Randomizer
  RANDOM_EXCLUDE_COUNT: 3,

  // Skeleton delay (ms) — chờ trước khi hiện skeleton
  SKELETON_DELAY: 250,

  // File HTML mở trực tiếp vẫn có thể xem demo; web server PHP dùng API thật.
  USE_MOCK: window.location.protocol === 'file:',

  // Placeholder image
  PLACEHOLDER_IMG: 'assets/img/placeholder.webp'
};
