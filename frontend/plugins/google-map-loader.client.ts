import { Loader } from '@googlemaps/js-api-loader';

export default defineNuxtPlugin(nuxtApp => {
  const loader = new Loader({
    apiKey: nuxtApp.$config.public.googleMapsApiKey as string,
    version: 'weekly',
    libraries: ['places'],
  });

  const createGoogleMap = async (element: HTMLElement, options: any) => {
    try {
      const google = await loader.load();

      return { google, googleMap: new google.maps.Map(element, options) }
    }
    catch (error) {
      console.error('Error loading Google Maps:', error);

      return null;
    }
  }

  nuxtApp.provide('createGoogleMap', createGoogleMap);
});
