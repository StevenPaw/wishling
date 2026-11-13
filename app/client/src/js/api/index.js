import { del, get, post, put } from './client';

export { ApiError } from './client';

const enc = encodeURIComponent;

export const authApi = {
    me: () => get('auth/me'),
    updateMe: (data) => put('auth/me', data),
    uploadAvatar: (blob) => {
        const data = new FormData();
        data.append('image', blob, 'profilbild.jpg');
        return post('auth/me/avatar', data);
    },
    deleteAvatar: () => del('auth/me/avatar'),
    requestLink: (email, redirect = '') => post('auth/request', { email, redirect }),
    verify: (token) => post('auth/verify', { token }),
    logout: () => post('auth/logout'),
};

export const wishlistApi = {
    list: () => get('wishlists'),
    create: (data) => post('wishlists', data),
    show: (hash) => get(`wishlists/${enc(hash)}`),
    update: (hash, data) => put(`wishlists/${enc(hash)}`, data),
    archive: (hash) => post(`wishlists/${enc(hash)}/archive`),
    restore: (hash) => post(`wishlists/${enc(hash)}/restore`),
    addWish: (hash, data) => post(`wishlists/${enc(hash)}/wishes`, data),
    duplicate: (hash, data) => post(`wishlists/${enc(hash)}/duplicate`, data),
};

export const wishApi = {
    update: (hash, data) => put(`wishes/${enc(hash)}`, data),
    archive: (hash) => post(`wishes/${enc(hash)}/archive`),
    restore: (hash) => post(`wishes/${enc(hash)}/restore`),
};

export const publicApi = {
    wishlist: (hash) => get(`public/wishlists/${enc(hash)}`),
    reserve: (hash, data) => post(`public/wishes/${enc(hash)}/reservation`, data),
    unreserve: (hash, data) => del(`public/wishes/${enc(hash)}/reservation`, data),
    addGift: (wishlistHash, data) => post(`public/wishlists/${enc(wishlistHash)}/gifts`, data),
    removeGift: (giftHash) => del(`public/gifts/${enc(giftHash)}`),
};
