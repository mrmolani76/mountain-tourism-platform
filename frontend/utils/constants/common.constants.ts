import { countries } from '@/config/countries.json';

export const COOKIE_MAX_AGE_1_YEAR = 365 * 24 * 60 * 60

export const MODULE_CORE = 'core'

export const TEXT_MAX_LIMIT: number = 255;
export const FIRST_AND_LAST_NAME_MAX_LIMIT: number = 127;
export const DELETED_REASON_MAX_TEXT_LIMIT: number = 512;
export const CONTACT_NUMBER_MAX_LIMIT: number = 7;
export const SHOW_MORE_MAX_LIMIT = 10;

export const ROLE_SUPER_ADMIN = 'super_admin';
export const ROLE_SUPER_ADMIN_ID = 1;
export const SUBMODULE_USERS = 'users';
const options = []
for (const country of countries) {
  options.push({
    title: country.name,
    value: country.iso2,
    states: country.states,
  })
}
export const countryOptions = options

export const VIEWER_MAX_FILE_SIZE = 100;
export const FILE_MAX_PAGE_COUNT = 500;
