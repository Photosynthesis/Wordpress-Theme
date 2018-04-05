module Directory.Messages exposing (..)

import Date exposing (Date)
import Directory.Communities exposing (Community)
import Directory.Pagination as Pagination
import Directory.Routing exposing (Route, FilterParam)


type Msg
    = SetCurrentDate Date
    | UrlChange Route
    | NavigateTo Route
    | UpdateSearchString String
    | SubmitSearchForm
    | CommunityPagination (Pagination.Msg Community)
