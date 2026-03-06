import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

export function useModalA11y({
  showLoginModal,
  showCreateSpotModal,
  loginModalRef,
  createSpotModalRef,
  closeLoginModal,
  closeCreateSpotModal,
}) {
  const lastFocusedElement = ref(null);

  const isAnyModalOpen = computed(() => showLoginModal.value || showCreateSpotModal.value);

  const activeModalElement = computed(() => {
    if (showCreateSpotModal.value) return createSpotModalRef.value;
    if (showLoginModal.value) return loginModalRef.value;
    return null;
  });

  function captureLastFocusedElement() {
    lastFocusedElement.value = document.activeElement instanceof HTMLElement ? document.activeElement : null;
  }

  function getFocusableElements(container) {
    if (!container) return [];
    const selector = [
      'a[href]',
      'button:not([disabled])',
      'input:not([disabled])',
      'select:not([disabled])',
      'textarea:not([disabled])',
      '[tabindex]:not([tabindex="-1"])',
    ].join(',');

    return Array.from(container.querySelectorAll(selector))
      .filter((element) => !element.hasAttribute('hidden') && element.getAttribute('aria-hidden') !== 'true');
  }

  async function focusFirstModalControl() {
    await nextTick();
    const modal = activeModalElement.value;
    if (!modal) return;

    const focusable = getFocusableElements(modal);
    if (focusable.length > 0) {
      focusable[0].focus();
      return;
    }
    modal.focus();
  }

  function trapModalFocus(event) {
    if (event.key !== 'Tab') return;
    const modal = activeModalElement.value;
    if (!modal) return;

    const focusable = getFocusableElements(modal);
    if (focusable.length === 0) {
      event.preventDefault();
      modal.focus();
      return;
    }

    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    const current = document.activeElement;

    if (event.shiftKey && current === first) {
      event.preventDefault();
      last.focus();
      return;
    }

    if (!event.shiftKey && current === last) {
      event.preventDefault();
      first.focus();
    }
  }

  function handleGlobalModalKeydown(event) {
    if (!isAnyModalOpen.value) return;

    if (event.key === 'Escape') {
      if (showCreateSpotModal.value) {
        closeCreateSpotModal();
        return;
      }
      if (showLoginModal.value) {
        closeLoginModal();
        return;
      }
    }

    trapModalFocus(event);
  }

  watch(
    () => isAnyModalOpen.value,
    async (isOpen) => {
      if (isOpen) {
        if (!lastFocusedElement.value) {
          captureLastFocusedElement();
        }
        await focusFirstModalControl();
        return;
      }

      if (lastFocusedElement.value instanceof HTMLElement) {
        await nextTick();
        lastFocusedElement.value.focus();
        lastFocusedElement.value = null;
      }
    },
  );

  watch(
    () => [showLoginModal.value, showCreateSpotModal.value],
    async ([loginOpen, createOpen]) => {
      if (loginOpen || createOpen) {
        await focusFirstModalControl();
      }
    },
  );

  onMounted(() => {
    document.addEventListener('keydown', handleGlobalModalKeydown);
  });

  onUnmounted(() => {
    document.removeEventListener('keydown', handleGlobalModalKeydown);
  });

  return {
    captureLastFocusedElement,
    isAnyModalOpen,
  };
}
