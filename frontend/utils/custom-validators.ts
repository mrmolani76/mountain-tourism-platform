// 👉 Dropdown Max Length Validator
export const maxSelectionValidator = (value: Array<number | string>, max: number) => {
  if (isEmpty(value) || !(max))
    return true

  return value.length <= max || `Do not select more than ${max} options.`
}

export const textMinMaxValidator = (value: unknown, min: number | undefined, max: number | undefined) => {
  const valueAsString = String(value)
  if (isEmpty(value) || !(min && max))
    return true;

  return (Number(min) <= valueAsString.length && Number(max) >= valueAsString.length) ? true : `Character length should be in between ${min} to ${max} characters.`;
}

export const textMinValidator = (value: unknown, min: number | undefined) => {
  const valueAsString = String(value)
  if (isEmpty(value) || isNullOrUndefined(min))
    return true;

  return (Number(min) <= valueAsString.length) ? true : `Character length should be in atleast ${min} characters.`;
}

export const textMaxValidator = (value: unknown, max: number | undefined) => {
  const valueAsString = String(value)
  if (isEmpty(value) || isNullOrUndefined(max))
    return true;

  return (Number(max) >= valueAsString.length) ? true : `Character length cannot be more than ${max} characters.`;
}

export const numericMinMaxValidator = (value: unknown, min: number | undefined, max: number | undefined) => {
  const valueAsNumber = Number(value)

  if (isEmpty(value) || (isNullOrUndefined(min) && isNullOrUndefined(max)))
    return true;

  return (Number(min) <= valueAsNumber && Number(max) >= valueAsNumber) ? true : `Enter number between ${min} and ${max}`;
}

export const numericMinValidator = (value: unknown, min: number | undefined) => {
  const valueAsNumber = Number(value)

  if (isEmpty(value) || isNullOrUndefined(min))
    return true;

  return (Number(min) <= valueAsNumber) ? true : `Please enter a greater than ${min}`;
}

export const numericMaxValidator = (value: unknown, max: number | undefined) => {
  const valueAsNumber = Number(value)

  if (isEmpty(value) || isNullOrUndefined(max))
    return true;

  return (Number(max) >= valueAsNumber) ? true : `Please enter a number no more than ${max}`;
}

// 👉 Length Validator
export const maxlengthValidator = (value: unknown, length: number) => {
  if (isEmpty(value) || !length)
    return true

  return String(value).length <= length || `The field value must not exceed ${length} characters`
}

export const disabledCheck = value => {

}
export const validatePassword = (password: unknown) => {
  if (isEmpty(password))
    return true;

  if (String(password).length < 8)
    return 'Password must be at least 8 characters long.';

  if (!/(\p{Ll}.*\p{Lu})|(\p{Lu}.*\p{Ll})/u.test(password))
    return 'Password must contain at least one uppercase and one lowercase letter.';

  if (!/\p{L}/u.test(password))
    return 'Password must contain at least one letter.';

  if (!/[\p{Z}\p{S}\p{P}]/u.test(password))
    return 'Password must not contain any special characters.';

  if (!/\p{N}/u.test(password))
    return 'Password must contain at least one number.';

  return true;
}

export const roleRequiredValidation = (role: unknown, minRequired: number | string, type: 'exact' | 'atleast') => {
  let roleLength = 0;
  if (!isNullOrUndefined(role))
    roleLength = role.length;

  if (minRequired === 'all' || isNullOrUndefined(minRequired))
    return true;

  if (type == 'exact' && roleLength != minRequired)
    return `Please select exact ${minRequired} options.`

  if (type == 'atleast' && roleLength < minRequired)
    return `Please select at least ${minRequired} options.`
}

export const arrayMinValidator = (value: unknown, min: number | undefined) => {
  const valueAsArray = value;

  if (!(min))
    return true;

  if (isNullOrUndefined(value))
    return `Please select at least ${min} options.`;

  return (Number(min) <= valueAsArray.length) ? true : `Please select at least ${min} options.`;
}

export const arrayMaxValidator = (value: unknown, max: number | undefined) => {
  const valueAsArray = value

  if (isEmpty(value) || !(max))
    return true;

  return (Number(max) >= valueAsArray.length) ? true : `Please do not select more than ${max} options.`;
}

export const alphaNumSpaceValidator = (value: string) => {
  if (isEmpty(value))
    return true;

  return /^[\w\s]+$/.test(String(value)) || 'Please enter only Alphabets and Numbers.'
}

export const numericValidator = (value: string) => {
  if (isEmpty(value))
    return true;

  if (/^((\d*)|((\d*)\.(\d*)))$/.test(value))
    return true;
  else
    return 'Please enter numeric value only.';
}

export const alphaNumericValidator = (value: unknown) => {
  if (isEmpty(value))
    return true

  return /^\w+$/.test(String(value)) || 'The field may only contain alphabetic and numeric characters.'
}

export const fileUploadMinValidator = (value: unknown, min: number | undefined) => {
  const valueAsArray = value;

  if (isNullOrUndefined(value) || !(min))
    return true;

  return (Number(min) <= valueAsArray.length) ? true : `Please upload at least ${min} file(s).`;
}

export const fileUploadMaxValidator = (value: unknown, max: number | undefined) => {
  const valueAsArray = value

  if (isEmpty(value) || !(max))
    return true;

  return (Number(max) >= valueAsArray.length) ? true : `Please do not upload more than ${max} file(s).`;
}

export const fileSizeMinValidator = (value: Array<File>, min: number | undefined) => {
  const valueAsArray = value

  if (isEmpty(value) || !(min))
    return true;

  for (const file of valueAsArray) {
    if ((file.size / 1000) < min) // divide by 1000 to convert file size from bytes to kb
      return `Please upload file with size more than ${min} KB.`
  }

  return true;
}

export const fileSizeMaxValidator = (value: Array<File>, max: number | undefined) => {
  const valueAsArray = value

  if (isEmpty(value) || !(max))
    return true;

  for (const file of valueAsArray) {
    if ((file.size / 1000) > max) // divide by 1000 to convert file size from bytes to kb
      return `Please upload file with size less than ${max} KB.`
  }

  return true;
}

export const uniqueOptionValidator = (value: string, options: Array<unknown>) => {
  if (isEmpty(value) || isEmpty(options))
    return true;

  if (isEmptyArray(options))
    return true;

  let countExistence = 0;
  for (const option of options) {
    if (option.title == value) {
      countExistence++;
      if (countExistence > 1)
        break;
    }
  }

  return (countExistence > 1) ? 'Duplicate option is not allowed.' : true;
}

export const previousCurrentPasswordChecker = (previousPassword: string, currentPassword: string) => {
  if (isEmpty(previousPassword) || isEmpty(currentPassword))
    return true;

  return (previousPassword == currentPassword) ? 'Your new password must be different from your previous password. Please choose a new, unique password.' : true;
}

export const zipCodeValidator = (value: string) => {
  if (isEmpty(value))
    return true;

  // Brunei postal code format: two uppercase letters followed by four digits (e.g., BB2713)
  const bruneiZipRegex = /^[A-Z]{2}\d{4}$/i;

  if (bruneiZipRegex.test(value.trim()))
    return true;
  else
    return 'Please enter a valid Brunei zip code (e.g., BB2713).';
};

export const phoneNumberValidator = (value: string) => {
  if (isEmpty(value))
    return true;

  if (/^[78]\d{6}$/.test(value))
    return true;
  else
    return 'Please enter valid number.';
}

export const userRoleDownGradeValidator = (value: boolean) => {
  return value ? 'Downgrading User role is not allowed, It is linked to other entities.' : true;
}

export const fileTypeValidation = (value: File, extensions: Array<string>) => {
  return !value || extensions.includes(value.type) || `Only ${extensions.join(', ')} files are allowed.`
}

export const dateRangeMaxValidator = (value: unknown) => {
  if (isEmpty(value))
    return true;

  return (value.includes('to')) ? true : 'Please select date range.';
}

// use this validator with type number field
export const numberRequired = (value: unknown) => {
  if (isEmpty(value))
    return 'Please enter a number.';

  return true;
}
export const userIsTrainingCoordinatorValidator = (value: boolean) => {
  return value ? 'Downgrading User role is not allowed, It is training coordinator.' : true;
}

export const alreadyExistsValidator = (exists: boolean) => {
  return exists
}

export const locationValidator = (value: string) => {
  if (!value || value.trim() === '')
    return true;

  // Match: latitude,longitude (both numbers, with optional +/- and decimals)
  const regex = /^\s*([+-]?(?:\d+(?:\.\d*)?|\.\d+))\s*,\s*([+-]?(?:\d+(?:\.\d*)?|\.\d+))\s*$/;

  const match = value.match(regex);
  if (!match)
    return 'Please enter a valid location in LAT,LNG format.';

  const lat = Number.parseFloat(match[1]);
  const lng = Number.parseFloat(match[2]);

  if (lat < -90 || lat > 90)
    return 'Latitude must be between -90 and 90.';

  if (lng < -180 || lng > 180)
    return 'Longitude must be between -180 and 180.';

  return true;
};
