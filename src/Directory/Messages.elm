module Directory.Messages exposing (..)

import Date exposing (Date)
import Directory.Communities exposing (CommunityListing, CommunityDetails, ImageData)
import Directory.Pagination as Pagination
import Directory.Routing exposing (Route, FilterParam)
import Gallery
import RemoteData exposing (WebData)


type Msg
    = SetCurrentDate Date
    | UrlChange Route
    | NavigateTo Route
      -- Listings
    | UpdateSearchString String
    | SubmitSearchForm
    | CommunityPagination (Pagination.Msg CommunityListing)
      -- Details
    | FetchCommunityDetails (WebData CommunityDetails)
    | GalleryMsg (Gallery.Msg ImageData)
