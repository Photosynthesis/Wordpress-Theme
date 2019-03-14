module Directory.Messages exposing (Msg(..))

import Date exposing (Date)
import Directory.Communities exposing (CommunityDetails, CommunityListing, ImageData)
import Directory.Pagination as Pagination
import Directory.Routing exposing (Route)
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
    | VerifyCommunityClicked
    | ValidateCommunity (WebData Bool)
