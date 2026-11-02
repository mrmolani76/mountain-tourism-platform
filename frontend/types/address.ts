export interface Address {
  address1: string
  address2: string
  zip: string
  city: string
  lat: number
  lng: number
}

export interface AddressForm extends Address {
  latLng: string
}
