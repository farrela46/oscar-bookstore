import { ref, provide, inject } from 'vue';

const eventBus = {
  handlers: ref({}),
  emit(event, ...args) {
    if (event in this.handlers.value) {
      this.handlers.value[event].forEach(handler => handler(...args));
    }
  },
  on(event, handler) {
    if (!(event in this.handlers.value)) {
      this.handlers.value[event] = [];
    }
    this.handlers.value[event].push(handler);
  }
};

export const provideEventBus = () => {
  provide('eventBus', eventBus);
};

export const useEventBus = () => {
  const bus = inject('eventBus');
  if (!bus) {
    throw new Error('Event bus is not provided');
  }
  return bus;
};

export default eventBus;
