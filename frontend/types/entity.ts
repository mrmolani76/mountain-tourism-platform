export interface Entity {
  uuid: string
  creator: LinkEntity
  updator: LinkEntity
  deletedAt: string
  createdAt: string
  updatedAt: string
  active: boolean
}
