export const formatPrice = (price: number) => {
    return "Rp " + Math.floor(price).toLocaleString('id-ID');
};
