<script setup lang="ts">
import marker from '~/assets/images/marker.png';

interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'onSubmit', payload: Address): void
}

interface Props {
  isReadonly: boolean
  isDialogVisible: boolean
  location: string | undefined
  selectedAddress: string | undefined
}

interface LatLngLiteral { lat: number;lng: number }

const props = defineProps<Props>();
const emit = defineEmits<Emit>();

let map: { google: any;googleMap: any } | null = null;
let autocomplete: any | null = null;
const AUTOCOMPLETE_INPUT_ID = 'app-text-field-autocomplete-input';
const address = ref<string | null>(null);
const nuxtApp = useNuxtApp();
const $createGoogleMap = nuxtApp.$createGoogleMap as (element: HTMLElement, options: any) => Promise<{ google: any;googleMap: any } | null>;

const latLng = ref<LatLngLiteral | null>(null);

const getLocation = (location: string | undefined): LatLngLiteral | null => {
  if (!location)
    return null;

  const [latString, lngString] = location.split(',');
  const lat = Number.parseFloat(latString);
  const lng = Number.parseFloat(lngString);

  if (Number.isFinite(lat) && Number.isFinite(lng))
    return { lat, lng };

  return null;
};

latLng.value = getLocation(props.location);

const initAutocomplete = () => {
  const currentMap = map;
  const input = document.getElementById(AUTOCOMPLETE_INPUT_ID) as HTMLInputElement | null;

  if (!input || !currentMap?.google || !currentMap.googleMap)
    return;

  if (autocomplete) {
    currentMap.google.maps.event.clearInstanceListeners(autocomplete);
    autocomplete.unbindAll?.();
    autocomplete = null;
  }

  autocomplete = new currentMap.google.maps.places.Autocomplete(input, {
    fields: ['geometry', 'formatted_address', 'address_components'],
    types: ['geocode'],
  });

  autocomplete.bindTo('bounds', currentMap.googleMap);
  currentMap.google.maps.event.clearListeners(autocomplete, 'place_changed');

  autocomplete.addListener('place_changed', () => {
    const place = autocomplete.getPlace();

    if (!place.geometry || !place.geometry.location)
      return;

    const lat = place.geometry.location.lat();
    const lng = place.geometry.location.lng();

    if (place.formatted_address)
      input.value = place.formatted_address;

    latLng.value = { lat, lng };
    setCenter(lat, lng);
  });
};

const setCenter = (lat: number | undefined, lng: number | undefined) => {
  if (lat && lng && map?.googleMap) {
    map.googleMap.setZoom(14);
    map.googleMap.setCenter({ lat, lng })
  }
}

watch(() => props.location, newLocation => {
  latLng.value = getLocation(newLocation);

  setCenter(latLng.value?.lat, latLng.value?.lng)
});

watch(() => props.isDialogVisible, async isOpen => {
  if (!isOpen)
    return;

  address.value = props.selectedAddress;

  // wait dom to complete
  await nextTick();

  let options = {};
  if (props.isReadonly) {
    options = {
      draggable: false, // disable dragging
      scrollwheel: true, // allow zoom with scroll
      disableDoubleClickZoom: false, // allow double click zoom
      gestureHandling: 'none', // prevents drag with mouse/touch
    }
  }

  map = await $createGoogleMap(document.getElementById('map'), {
    center: { lat: 4.47865448568356, lng: 114.652410771153 },
    zoom: 12,
    zoomControl: true,
    disableDefaultUI: true,
    fullscreenControl: false,
    streetViewControl: false,
    rotateControl: false,
    mapTypeControl: false,
    tilt: 0,
    ...options,
  });

  const currentMap = map;

  if (!currentMap)
    return;

  initAutocomplete();

  currentMap.googleMap.addListener('idle', () => {
    const center = currentMap.googleMap.getCenter();

    if (center) {
      latLng.value = {
        lat: center.lat(),
        lng: center.lng(),
      };
    }
  });

  const location = getLocation(props.location);

  setCenter(location?.lat, location?.lng);
});

const onSubmit = () => {
  const locationToSubmit = latLng.value;

  if (!locationToSubmit || !map?.google)
    return;

  const geocoder = new map.google.maps.Geocoder();

  geocoder.geocode({ location: locationToSubmit }, (results: any, status: string) => {
    if (status !== 'OK' || !results?.[0]) {
      emit('onSubmit', {
        ...locationToSubmit,
        address1: '',
        address2: '',
        city: '',
        zip: '',
      });
      emit('update:isDialogVisible', false);

      return;
    }

    const addressComponents = results[0].address_components;

    // Helper: get component by type
    const getComponent = (type: string) => {
      const comp = addressComponents.find((c: string) => c.types.includes(type));

      return comp ? comp.long_name : '';
    };

    // Extract data
    const streetNumber = getComponent('street_number');
    const route = getComponent('route');
    const city = getComponent('locality') || getComponent('administrative_area_level_2');
    const state = getComponent('administrative_area_level_1');
    const postalCode = getComponent('postal_code');
    const country = getComponent('country');

    emit('onSubmit', {
      ...latLng.value,
      address1: `${streetNumber} ${route}`,
      address2: `${state}, ${country}`,
      city,
      zip: postalCode,
    })
    emit('update:isDialogVisible', false)
  });
}

const resetMapAndAutocomplete = () => {
  if (map?.google)
    map.google.maps.event.clearInstanceListeners(map.googleMap);

  map = null;

  if (autocomplete) {
    autocomplete.unbindAll?.();
    autocomplete = null;
  }
};

const onCancel = () => {
  resetMapAndAutocomplete();
  emit('update:isDialogVisible', false);
};

watch(() => props.isDialogVisible, isOpen => {
  if (!isOpen)
    resetMapAndAutocomplete();
});
</script>

<template>
  <VDialog
    width="900"
    :modelValue="props.isDialogVisible"
    @update:modelValue="(val) => $emit('update:isDialogVisible', val)"
  >
    <VCard :title="isReadonly ? $t('location') : $t('selectLocation')">
      <VForm>
        <VCardText>
          <VRow>
            <VCol cols="12">
              <div>
                <AppTextField
                  id="autocomplete-input"
                  v-model="address"
                  type="text"
                  :readonly="isReadonly"
                  :placeholder="$t('searchForALocation')"
                  :disabled="isReadonly"
                  @keydown.enter.prevent
                />
              </div>
            </VCol>
          </VRow>
          <VRow>
            <VCol cols="12">
              <div
                v-if="isDialogVisible"
                class="map-container"
              >
                <div
                  id="map"
                  style="width: 100%; height: 100%"
                />

                <div class="marker-fixed">
                  <img
                    :src="marker"
                    alt="Marker"
                    width="50px"
                    class="w-8 h-8"
                  >
                </div>
              </div>
            </VCol>
          </VRow>
        </VCardText>

        <VCardText class="d-flex justify-end flex-wrap gap-3 tw-mt-4">
          <div class="d-flex justify-end flex-wrap gap-4">
            <VBtn
              color="secondary"
              variant="tonal"
              @click="onCancel"
            >
              {{ isReadonly ? $t('close') : $t('cancel') }}
            </VBtn>
            <VBtn
              v-if="!isReadonly"
              type="button"
              @click="onSubmit"
            >
              Submit
            </VBtn>
          </div>
        </VCardText>
      </VForm>
    </VCard>
  </VDialog>
</template>

<style scoped>
.map-container {
  position: relative;
  width: 100%;
  height: 500px;
}

/* Floating marker overlay */
.marker-fixed {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -100%); /* adjust for pin tip */
  pointer-events: none; /* so you can still drag the map */
}
</style>
