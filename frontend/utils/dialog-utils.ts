export const showConfirmDialog = (
  message: string | undefined = undefined,
  confirm: string | undefined = undefined,
  cancel: string | undefined = undefined,
) => {
  return (window as any).showConfirmDialog(message, confirm, cancel);
}

export const showAlertDialog = (
  title: string | undefined = undefined,
  message: string | undefined = undefined,
  actionLabel: string | undefined = undefined,
) => {
  return (window as any).showAlertDialog(title, message, actionLabel);
}

export const showBulkActionProgress = (
  channel: string,
  event?: string,
) => {
  return (window as any).showBulkActionProgress(channel, event)
}
