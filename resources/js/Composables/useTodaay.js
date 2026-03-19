export default function useToday() {
    const today = new Date().toISOString().split('T')[0];
    return today;
}
