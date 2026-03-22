/**
 * デザイントークン定義
 * アプリケーション全体で使用する色、間隔、ブレークポイントを定義
 */

export const colors = {
  primary: '#667eea',
  primaryDark: '#764ba2',
  success: '#28a745',
  danger: '#dc3545',
  warning: '#ffc107',
};

export const spacing = {
  xs: '0.25rem',   // 4px
  sm: '0.5rem',    // 8px
  md: '1rem',      // 16px
  lg: '1.5rem',    // 24px
  xl: '2rem',      // 32px
  '2xl': '3rem',   // 48px
};

export const breakpoints = {
  xs: '480px',
  sm: '640px',
  md: '768px',
  lg: '1024px',
  xl: '1280px',
  '2xl': '1536px',
};

export default {
  colors,
  spacing,
  breakpoints,
};
