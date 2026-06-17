import api from './client';

export const authApi = {
  login: (data) => api.post('/login', data),
  register: (data) => api.post('/register', data),
  verifyEmail: (data) => api.post('/verify-email', data),
  resendCode: (email) => api.post('/verify-email/resend', { email }),
  forgotPassword: (data) => api.post('/forgot-password', data),
  resetPassword: (data) => api.post('/reset-password', data),
  getProfile: () => api.get('/account'),
  updateProfile: (data) => api.put('/account', data),
  updatePassword: (data) => api.put('/account/password', data),
  confirmPasswordChange: (data) => api.post('/account/password/confirm', data),
  updatePhoto: (data) =>
    api.post('/account/photo', data, {
      headers: { 'Content-Type': 'multipart/form-data' },
    }),
  getStats: () => api.get('/me/stats'),
};
