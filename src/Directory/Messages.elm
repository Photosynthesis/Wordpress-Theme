module Directory.Messages exposing (Msg(..))

import Directory.Communities exposing (CommunityDetails, CommunityListing, ImageData)
import Directory.Pagination as Pagination
import Directory.Routing exposing (Route)
import Gallery
import RemoteData exposing (WebData)
import Time exposing (Posix, Zone)


type Msg
    = SetCurrentDate ( Posix, Zone )
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
