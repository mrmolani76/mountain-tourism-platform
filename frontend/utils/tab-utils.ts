export const pushHashIntoRoute = (hash: string | any) => {
  const router = useRouter();

  router.push({ hash: `#${hash}` })
}
